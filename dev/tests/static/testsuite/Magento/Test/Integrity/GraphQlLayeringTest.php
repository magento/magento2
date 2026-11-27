<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

/**
 * Checks that domain modules do not depend on the GraphQL layer.
 */
class GraphQlLayeringTest extends TestCase
{
    private const GRAPHQL_NAMESPACE_PATTERN =
        '/\bMagento\\\\(?:Framework\\\\GraphQl[A-Za-z]*|[A-Za-z]*GraphQl[A-Za-z]*)\\\\/';

    /**
     * Collects all unwhitelisted GraphQL dependencies in domain modules.
     *
     * @return void
     */
    public function testDomainModulesHaveNoGraphQlCoupling(): void
    {
        $whitelist = $this->getWhitelist();
        $violations = [];
        $registrar = new ComponentRegistrar();
        foreach ($registrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $moduleDir) {
            if (preg_match('/GraphQl/', $moduleName)) {
                continue;
            }

            $dependencies = $this->getPhpDependencies($moduleDir) + $this->getConfigurationDependencies($moduleDir);
            foreach ($dependencies as $path => $rule) {
                $entry = $moduleName . ':' . $path;
                if (!isset($whitelist[$entry])) {
                    $violations[] = '(' . $rule . ') ' . $entry;
                }
            }
        }

        sort($violations);
        self::assertSame([], $violations, "GraphQL coupling in domain modules:\n" . implode("\n", $violations));
    }

    /**
     * Finds PHP files referencing GraphQL namespaces, including docblocks.
     *
     * @param string $moduleDir
     * @return array
     */
    private function getPhpDependencies(string $moduleDir): array
    {
        $dependencies = [];
        foreach (Files::getFiles([$moduleDir], '*.php') as $file) {
            $relativePath = substr($file, strlen($moduleDir) + 1);
            if (preg_match('#(^|/)Test/#', $relativePath)) {
                continue;
            }
            if (preg_match(self::GRAPHQL_NAMESPACE_PATTERN, file_get_contents($file))) {
                $dependencies[$relativePath] = 'a';
            }
        }
        return $dependencies;
    }

    /**
     * Finds GraphQL configuration directories and declared dependencies.
     *
     * @param string $moduleDir
     * @return array
     */
    private function getConfigurationDependencies(string $moduleDir): array
    {
        $dependencies = [];
        if (is_dir($moduleDir . '/etc/graphql')) {
            $dependencies['etc/graphql'] = 'b';
        }
        if ($this->hasGraphQlSequence($moduleDir . '/etc/module.xml')) {
            $dependencies['module.xml'] = 'c';
        }
        if ($this->hasGraphQlRequirement($moduleDir . '/composer.json')) {
            $dependencies['composer.json'] = 'd';
        }
        return $dependencies;
    }

    /**
     * Checks module sequence entries for GraphQL modules.
     *
     * @param string $file
     * @return bool
     */
    private function hasGraphQlSequence(string $file): bool
    {
        if (!is_file($file)) {
            return false;
        }
        $config = simplexml_load_file($file);
        foreach ($config->xpath('/config/module/sequence/module') as $module) {
            if (preg_match('/GraphQl/', (string)$module['name'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks Composer runtime requirements for GraphQL packages.
     *
     * @param string $file
     * @return bool
     */
    private function hasGraphQlRequirement(string $file): bool
    {
        if (!is_file($file)) {
            return false;
        }
        $composer = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        foreach (array_keys($composer['require'] ?? []) as $package) {
            if (preg_match('/graph-?ql/', $package)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reads module-relative exceptions, ignoring blank lines and comments.
     *
     * @return array
     */
    private function getWhitelist(): array
    {
        $whitelist = [];
        $lines = file(__DIR__ . '/_files/graphql_layering/whitelist.txt', FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $entry = trim(explode('#', $line, 2)[0]);
            if ($entry !== '') {
                $whitelist[$entry] = true;
            }
        }
        return $whitelist;
    }
}
