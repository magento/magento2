<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

/**
 * Class PreferencesResolving
 *
 * @package Magento\Setup\Module\Di\Compiler\Config\Chain
 */
class PreferencesResolving implements ModificationInterface
{
    /**
     * Argument keys which require recursive resolving
     */
    private const RECURSIVE_ARGUMENT_KEYS = [
        '_i_' => true, // shared instance of a class or interface
        '_ins_' => true // non-shared instance of a class or interface
    ];

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        if (!isset($config['arguments'], $config['preferences'])) {
            return $config;
        }

        $this->resolvePreferences($config['arguments'], $config['preferences']);

        return $config;
    }

    /**
     * Replaces interfaces to their concrete implementations in scope of current config
     *
     * @param array $argument
     * @param array $preferences
     */
    private function resolvePreferences(&$argument, &$preferences)
    {
        if (!is_array($argument)) {
            return;
        }

        foreach ($argument as $key => &$value) {
            if (isset(self::RECURSIVE_ARGUMENT_KEYS[$key])) {
                $value = $this->resolvePreferenceRecursive($value, $preferences);
                continue;
            }

            $this->resolvePreferences($value, $preferences);
        }
    }

    /**
     * Resolves preference recursively
     *
     * @param string $value
     * @param array $preferences
     *
     * @return string
     */
    private function resolvePreferenceRecursive(&$value, &$preferences)
    {
        return isset($preferences[$value])
            ? $this->resolvePreferenceRecursive($preferences[$value], $preferences)
            : $value;
    }
}
