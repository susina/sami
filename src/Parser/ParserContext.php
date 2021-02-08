<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\Parser;

use Sami\Parser\Filter\FilterInterface;
use Sami\Reflection\ClassReflection;

// @todo add types where there aren't
class ParserContext
{
    protected FilterInterface $filter;
    protected DocBlockParser $docBlockParser;
    protected $prettyPrinter;
    protected array $errors = [];
    protected $namespace;

    /** @var string[] */
    protected array $aliases = [];
    protected ?ClassReflection $class = null;
    protected $file;
    protected ?string $hash = null;

    /** @var ClassReflection[] */
    protected array $classes;

    public function __construct(FilterInterface $filter, DocBlockParser $docBlockParser, $prettyPrinter)
    {
        $this->filter = $filter;
        $this->docBlockParser = $docBlockParser;
        $this->prettyPrinter = $prettyPrinter;
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    /**
     * @return DocBlockParser
     */
    public function getDocBlockParser(): DocBlockParser
    {
        return $this->docBlockParser;
    }

    public function getPrettyPrinter()
    {
        return $this->prettyPrinter;
    }

    public function addAlias(string $alias, string $name): void
    {
        $this->aliases[$alias] = $name;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function enterFile($file, $hash)
    {
        $this->file = $file;
        $this->hash = $hash;
        $this->errors = [];
        $this->classes = [];
    }

    public function leaveFile(): array
    {
        $this->hash = null;
        $this->file = null;
        $this->errors = [];

        return $this->classes;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function addErrors(string $name, int $line, array $errors): void
    {
        foreach ($errors as $error) {
            $this->addError($name, $line, $error);
        }
    }

    public function addError(string $name, int $line, string $error): void
    {
        $this->errors[] = sprintf('%s on "%s" in %s:%d', $error, $name, $this->file, $line);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function enterClass(ClassReflection $class): void
    {
        $this->class = $class;
    }

    public function leaveClass(): void
    {
        if (null === $this->class) {
            return;
        }

        $this->classes[] = $this->class;
        $this->class = null;
    }

    public function getClass(): ?ClassReflection
    {
        return $this->class;
    }

    public function enterNamespace($namespace)
    {
        $this->namespace = $namespace;
        $this->aliases = [];
    }

    public function leaveNamespace(): void
    {
        $this->namespace = null;
        $this->aliases = [];
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}
