<?php declare(strict_types=1);

/*
 * This file is part of the Sami utility.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Susina\Sami;

class Tree
{
    public function getTree(Project $project): array
    {
        $namespaces = [];
        $ns = $project->getConfig('simulate_namespaces') ? $project->getSimulatedNamespaces() : $project->getNamespaces();
        foreach ($ns as $namespace) {
            if (false !== $pos = strpos($namespace, '\\')) {
                $namespaces[substr($namespace, 0, $pos)][] = $namespace;
            } else {
                $namespaces[$namespace][] = $namespace;
            }
        }

        return $this->generateClassTreeLevel($project, 1, $namespaces, []);
    }

    protected function generateClassTreeLevel(Project $project, int $level, array $namespaces, array $classes): array
    {
        ++$level;

        $tree = [];
        foreach ($namespaces as $namespace => $subnamespaces) {
            // classes
            if ($project->getConfig('simulate_namespaces')) {
                $cl = $project->getSimulatedNamespaceAllClasses($namespace);
            } else {
                $cl = $project->getNamespaceAllClasses($namespace);
            }

            // subnamespaces
            $ns = [];
            foreach ($subnamespaces as $subnamespace) {
                $parts = explode('\\', $subnamespace);
                if (!isset($parts[$level - 1])) {
                    continue;
                }

                $ns[implode('\\', array_slice($parts, 0, $level))][] = $subnamespace;
            }

            $parts = explode('\\', $namespace);
            $url = '';
            if (!$project->getConfig('simulate_namespaces')) {
                $url = $parts[count($parts) - 1]
                    && $project->hasNamespace($namespace)
                    && (count($subnamespaces) || count($cl)) ? $namespace : '';
            }
            $short = $parts[count($parts) - 1] ? $parts[count($parts) - 1] : '[Global Namespace]';

            $tree[] = [$short, $url, $this->generateClassTreeLevel($project, $level, $ns, $cl)];
        }

        foreach ($classes as $class) {
            if ($project->getConfig('simulate_namespaces')) {
                $parts = explode('_', $class->getShortName());
                $short = array_pop($parts);
            } else {
                $short = $class->getShortName();
            }

            $tree[] = [$short, $class, []];
        }

        return $tree;
    }
}
