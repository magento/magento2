<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Phrase;

/**
 * Sanitizes data received from UI data providers.
 */
class Sanitizer
{
    /**
     * Extract rendering config from given UI data.
     *
     * @param array $data
     * @return bool|array
     */
    private function extractConfig(array $data)
    {
        /** @var array|bool $config */
        $config = [];
        if (array_key_exists('__disableTmpl', $data)) {
            //UI data provider has explicitly provided rendering config.
            $config = $data['__disableTmpl'];
            unset($data['__disableTmpl']);
        }

        return $config;
    }

    /**
     * Sanitizes data from a UI data provider.
     *
     * @param array $data
     * @return array
     */
    public function sanitize(array $data): array
    {
        $config = $this->extractConfig($data);
        foreach ($data as $key => $datum) {
            if (is_array($datum)) {
                //Each array must have its own __disableTmpl property
                $data[$key] = $this->sanitize($datum);
            } elseif (!is_bool($config)
                && !array_key_exists($key, $config)
                && (is_string($datum) || $datum instanceof Phrase)
                && preg_match('/\$\{.+\}/', (string)$datum)
            ) {
                //Templating is not disabled for all properties or for this property specifically
                //Property is a string that contains template syntax, so we are disabling its rendering
                $config[$key] = true;
            }
        }
        if ($config !== []) {
            //Some properties require rendering configuration.
            $data['__disableTmpl'] = $config;
        }

        return $data;
    }

    /**
     * Sanitize a component's metadata.
     *
     * Will sanitize full component's metadata as well as metadata of it's child components.
     *
     * @param array $meta
     * @return array
     */
    public function sanitizeComponentMetadata(array $meta): array
    {
        if (array_key_exists('arguments', $meta)
            && is_array($meta['arguments'])
            && array_key_exists('data', $meta['arguments'])
            && is_array($meta['arguments']['data'])
            && array_key_exists('config', $meta['arguments']['data'])
            && is_array($meta['arguments']['data']['config'])
        ) {
            $meta['arguments']['data']['config'] = $this->sanitize($meta['arguments']['data']['config']);
        }
        if (array_key_exists('children', $meta) && is_array($meta['children'])) {
            $meta['children'] = array_map([$this, 'sanitizeComponentMetadata'], $meta['children']);
        }

        return $meta;
    }
}
