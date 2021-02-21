<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Parser;

use Susina\Sami\Message;
use Susina\Sami\Project;
use Susina\Sami\Reflection\ClassReflection;
use Susina\Sami\Reflection\LazyClassReflection;
use Susina\Sami\Store\StoreInterface;
use Symfony\Component\Finder\Finder;

class Parser
{
    protected StoreInterface $store;
    protected Finder $iterator;
    protected CodeParser $parser;
    protected ClassTraverser $traverser;

    public function __construct(string|Finder $iterator, StoreInterface $store, CodeParser $parser, ClassTraverser $traverser)
    {
        $this->iterator = $this->createIterator($iterator);
        $this->store = $store;
        $this->parser = $parser;
        $this->traverser = $traverser;
    }

    public function parse(Project $project, ?callable $callback = null): Transaction
    {
        $step = 0;
        $steps = iterator_count($this->iterator);
        $context = $this->parser->getContext();
        $transaction = new Transaction($project);
        $toStore = new \SplObjectStorage();
        foreach ($this->iterator as $file) {
            ++$step;

            $code = file_get_contents($file->getPathname());
            $hash = sha1($code);
            if ($transaction->hasHash($hash)) {
                continue;
            }

            $context->enterFile((string) $file, $hash);

            $this->parser->parse($code);

            if (null !== $callback) {
                call_user_func($callback, Message::PARSE_ERROR, $context->getErrors());
            }

            foreach ($context->leaveFile() as $class) {
                if (null !== $callback) {
                    call_user_func($callback, Message::PARSE_CLASS, [floor($step / $steps * 100), $class]);
                }

                $project->addClass($class);
                $transaction->addClass($class);
                $toStore->attach($class);
                $class->notFromCache();
            }
        }

        // cleanup
        foreach ($transaction->getRemovedClasses() as $class) {
            $project->removeClass(new LazyClassReflection($class));
            $this->store->removeClass($project, $class);
        }

        // visit each class for stuff that can only be done when all classes are parsed
        $toStore->addAll($this->traverser->traverse($project));

        /** @var ClassReflection $class */
        foreach ($toStore as $class) {
            $this->store->writeClass($project, $class);
        }

        return $transaction;
    }

    private function createIterator(string|Finder $iterator): Finder
    {
        if (is_string($iterator)) {
            $it = new Finder();
            $it->files()->name('*.php')->in($iterator);

            return $it;
        }

        return $iterator;
    }
}
