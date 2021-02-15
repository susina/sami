<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sami\RemoteRepository;

class GitLabRemoteRepository extends AbstractRemoteRepository
{
    protected $url = 'https://gitlab.com/';

    public function __construct(string $name, string $localPath, string $url = '')
    {
        if ($url !== '') {
            $this->url = $url;
        }

        parent::__construct($name, $localPath);
    }

    public function getFileUrl(string $projectVersion, string $relativePath, int $line): string
    {
        $url = $this->url.$this->name.'/blob/'.str_replace('\\', '/', $projectVersion.$relativePath);

        if (null !== $line) {
            $url .= '#L'.(int) $line;
        }

        return $url;
    }
}
