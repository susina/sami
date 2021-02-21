<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami\Console;

use Susina\Sami\Console\Command\ParseCommand;
use Susina\Sami\Console\Command\RenderCommand;
use Susina\Sami\Console\Command\UpdateCommand;
use Susina\Sami\Sami;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('Sami', Sami::VERSION);

        $this->add(new UpdateCommand());
        $this->add(new ParseCommand());
        $this->add(new RenderCommand());
    }

    public function getLongVersion(): string
    {
        return parent::getLongVersion().' by <comment>Fabien Potencier</comment>';
    }
}
