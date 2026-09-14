<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * Identifies plugins declared only against targets that do not exist.
 *
 * Populated from plugin config that Interception already loads during compile,
 * then used by area definition collection to skip constructor resolution.
 */
class OrphanedPluginList
{
    /**
     * pluginClass => [targetClass => true, ...]
     *
     * @var array<string, array<string, true>>
     */
    private array $pluginToTargets = [];

    /**
     * Collects plugin-to-target mappings from already-loaded plugin list data.
     *
     * @param array $pluginData type => [pluginName => ['instance' => class, ...], ...]
     * @return void
     */
    public function collectFromPluginData(array $pluginData): void
    {
        foreach ($pluginData as $type => $plugins) {
            if (!is_array($plugins)) {
                continue;
            }
            $type = ltrim((string) $type, '\\');
            foreach ($plugins as $pluginConfig) {
                if (!isset($pluginConfig['instance'])) {
                    continue;
                }
                $pluginClass = ltrim((string) $pluginConfig['instance'], '\\');
                $this->pluginToTargets[$pluginClass][$type] = true;
            }
        }
    }

    /**
     * Returns whether the given class is a plugin declared only against missing targets.
     *
     * @param string $pluginClass
     * @return bool
     */
    public function isOrphanedPlugin(string $pluginClass): bool
    {
        $pluginClass = ltrim($pluginClass, '\\');
        if (!isset($this->pluginToTargets[$pluginClass])) {
            return false;
        }

        foreach (array_keys($this->pluginToTargets[$pluginClass]) as $target) {
            if (class_exists($target) || interface_exists($target)) {
                return false;
            }
        }

        return true;
    }
}
