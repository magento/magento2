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
     * Normalized virtual type names.
     *
     * @var array<string, true>
     */
    private array $virtualTypeNames = [];

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
     * Records virtual type names so they are treated as valid plugin targets.
     *
     * @param array $virtualTypes virtual type name => original type
     * @return void
     */
    public function collectVirtualTypes(array $virtualTypes): void
    {
        foreach (array_keys($virtualTypes) as $virtualType) {
            $this->virtualTypeNames[ltrim((string) $virtualType, '\\')] = true;
        }
    }

    /**
     * Returns whether the given class is a plugin declared only against missing targets.
     *
     * Virtual types are valid targets even though they are not real PHP classes.
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
            if ($this->isDeclaredTarget($target)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether a plugin target exists as a class, interface, or virtual type.
     *
     * @param string $target
     * @return bool
     */
    private function isDeclaredTarget(string $target): bool
    {
        return class_exists($target)
            || interface_exists($target)
            || isset($this->virtualTypeNames[$target]);
    }
}
