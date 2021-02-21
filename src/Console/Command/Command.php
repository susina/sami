<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Console\Command;

use Susina\Sami\Message;
use Susina\Sami\Parser\Transaction;
use Susina\Sami\Project;
use Susina\Sami\Version\Version;
use Susina\Sami\Renderer\Diff;
use Susina\Sami\Sami;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class Command extends BaseCommand
{
    const PARSE_ERROR = 64;

    protected Sami $sami;
    protected Version $version;
    protected bool $started;
    protected array $diffs = [];
    protected array $transactions = [];
    protected array $errors = [];
    protected InputInterface $input;
    protected OutputInterface $output;

    /**
     * @see Command
     */
    protected function configure(): void
    {
        $this->getDefinition()->addArgument(new InputArgument('config', InputArgument::REQUIRED, 'The configuration'));
        $this->getDefinition()->addOption(new InputOption('only-version', '', InputOption::VALUE_REQUIRED, 'The version to build'));
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;

        $config = $input->getArgument('config');
        $filesystem = new Filesystem();

        if (!$filesystem->isAbsolutePath($config)) {
            $config = getcwd().'/'.$config;
        }

        if (!is_file($config)) {
            throw new \InvalidArgumentException(sprintf('Configuration file "%s" does not exist.', $config));
        }

        $this->sami = $this->loadSami($config);

        if ($input->getOption('only-version')) {
            $this->sami['versions'] = $input->getOption('only-version');
        }

        if (!$this->sami instanceof Sami) {
            throw new \RuntimeException(sprintf('Configuration file "%s" must return a Sami instance.', $config));
        }
    }

    public function update(Project $project): int
    {
        $callback = $this->output->isDecorated() ? [$this, 'messageCallback'] : null;

        $project->update($callback, $this->input->getOption('force'));

        $this->displayParseSummary();
        $this->displayRenderSummary();

        return count($this->errors) ? self::PARSE_ERROR : 0;
    }

    public function parse(Project $project): int
    {
        $project->parse([$this, 'messageCallback'], $this->input->getOption('force'));

        $this->displayParseSummary();

        return count($this->errors) ? self::PARSE_ERROR : 0;
    }

    public function render(Project $project): int
    {
        $project->render([$this, 'messageCallback'], $this->input->getOption('force'));

        $this->displayRenderSummary();

        return count($this->errors) ? self::PARSE_ERROR : 0;
    }

    public function messageCallback(string $message, $data)
    {
        switch ($message) {
            case Message::PARSE_CLASS:
                [$progress, $class] = $data;
                $this->displayParseProgress($progress, $class);
                break;
            case Message::PARSE_ERROR:
                $this->errors = array_merge($this->errors, $data);
                break;
            case Message::SWITCH_VERSION:
                $this->version = $data;
                $this->errors = [];
                $this->started = false;
                $this->displaySwitch();
                break;
            case Message::PARSE_VERSION_FINISHED:
                $this->transactions[(string) $this->version] = $data;
                $this->displayParseEnd($data);
                $this->started = false;
                break;
            case Message::RENDER_VERSION_FINISHED:
                $this->diffs[(string) $this->version] = $data;
                $this->displayRenderEnd($data);
                $this->started = false;
                break;
            case Message::RENDER_PROGRESS:
                [$section, $message, $progression] = $data;
                $this->displayRenderProgress($section, $message, $progression);
                break;
        }
    }

    public function renderProgressBar($percent, $length): string
    {
        return
            str_repeat('#', (int) floor($percent / 100 * $length))
            .sprintf(' %d%%', $percent)
            .str_repeat(' ', $length - (int) floor($percent / 100 * $length))
        ;
    }

    public function displayParseProgress($progress, $class): void
    {
        if ($this->started) {
            $this->output->isDecorated() and $this->output->write("\033[2A");
        }
        $this->started = true;

        $this->output->isDecorated() and $this->output->write(
            sprintf(
            "  Parsing <comment>%s</comment>%s\033[K\n          %s\033[K\n",
            $this->renderProgressBar($progress, 50),
            count($this->errors) ? ' <fg=red>'.count($this->errors).' error'.(1 == count($this->errors) ? '' : 's').'</>' : '',
            $class->getName()
        )
        );
    }

    public function displayRenderProgress($section, $message, $progression): void
    {
        if ($this->started) {
            $this->output->isDecorated() and $this->output->write("\033[2A");
        }
        $this->started = true;

        $this->output->isDecorated() and $this->output->write(sprintf(
            "  Rendering <comment>%s</comment>\033[K\n            <info>%s</info> %s\033[K\n",
            $this->renderProgressBar($progression, 50),
            $section,
            $message
        ));
    }

    public function displayParseEnd(Transaction $transaction): void
    {
        if (!$this->started) {
            return;
        }

        $this->output->isDecorated() and $this->output->write(sprintf("\033[2A<info>  Parsing   done</info>\033[K\n\033[K\n\033[1A", count($this->errors) ? ' <fg=red>'.count($this->errors).' errors</>' : ''));

        if ($this->input->getOption('verbose') && count($this->errors)) {
            foreach ($this->errors as $error) {
                $this->output->write(sprintf('<fg=red>ERROR</>: '));
                $this->output->writeln($error, OutputInterface::OUTPUT_RAW);
            }
            $this->output->writeln('');
        }
    }

    public function displayRenderEnd(Diff $diff): void
    {
        if (!$this->started) {
            return;
        }

        $this->output->isDecorated() and $this->output->write("\033[2A<info>  Rendering done</info>\033[K\n\033[K\n\033[1A");
    }

    public function displayParseSummary(): void
    {
        if (count($this->transactions) <= 0) {
            return;
        }

        $this->output->writeln('');
        $this->output->writeln('<bg=cyan;fg=white> Version </>  <bg=cyan;fg=white> Updated C </>  <bg=cyan;fg=white> Removed C </>');

        foreach ($this->transactions as $version => $transaction) {
            $this->output->writeln(sprintf('%9s  %11d  %11d', $version, count($transaction->getModifiedClasses()), count($transaction->getRemovedClasses())));
        }
        $this->output->writeln('');
    }

    public function displayRenderSummary(): void
    {
        if (count($this->diffs) <= 0) {
            return;
        }

        $this->output->writeln('<bg=cyan;fg=white> Version </>  <bg=cyan;fg=white> Updated C </>  <bg=cyan;fg=white> Updated N </>  <bg=cyan;fg=white> Removed C </>  <bg=cyan;fg=white> Removed N </>');

        foreach ($this->diffs as $version => $diff) {
            $this->output->writeln(sprintf(
                '%9s  %11d  %11d  %11d  %11d',
                $version,
                count($diff->getModifiedClasses()),
                count($diff->getModifiedNamespaces()),
                count($diff->getRemovedClasses()),
                count($diff->getRemovedNamespaces())
            ));
        }
        $this->output->writeln('');
    }

    public function displaySwitch(): void
    {
        $this->output->writeln(sprintf("\n<fg=cyan>Version %s</>", $this->version));
    }

    private function loadSami(string $config): Sami
    {
        return require $config;
    }
}