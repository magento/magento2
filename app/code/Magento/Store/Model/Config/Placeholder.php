<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Store\Model\Config;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 */
class Placeholder implements PlaceholderInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string[]
     */
    protected $urlPaths;

    /**
     * @var string
     */
    protected $urlPlaceholder;

    /**
     * @param RequestInterface $request
     * @param string[] $urlPaths
     * @param string $urlPlaceholder
     */
    public function __construct(RequestInterface $request, $urlPaths, $urlPlaceholder)
    {
        $this->request        = $request;
        $this->urlPaths       = $urlPaths;
        $this->urlPlaceholder = $urlPlaceholder;
    }

    /**
     * Replace placeholders with config values
     *
     * @param array $data
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(array $data = [])
    {
        if (empty($data)) {
            return [];
        }
        array_walk_recursive(
            $data,
            function (&$value, $key, $data) {
                if (is_string($value) && str_contains($value, '{')) {  // If _getPlaceholder() would do nothing, skip
                    $value = $this->_processPlaceholders($value, $data);
                }
            },
            $data
        );
        return $data;
    }

    /**
     * Process array data recursively
     *
     * @deprecated 101.0.4 This method isn't used in process() implementation anymore
     * @see process()
     *
     * @param array &$data
     * @param string $path
     * @return void
     * @throws LocalizedException
     */
    protected function _processData(&$data, $path)
    {
        $configValue = $this->_getValue($path, $data);
        if (is_array($configValue)) {
            foreach (array_keys($configValue) as $key) {
                $this->_processData($data, $path . '/' . $key);
            }
        } else {
            $this->_setValue($data, $path, $this->_processPlaceholders($configValue, $data));
        }
    }

    /**
     * Replace placeholders with config values
     *
     * @param string $value
     * @param array $data
     * @return string
     * @throws LocalizedException
     */
    protected function _processPlaceholders($value, $data)
    {
        $placeholder = $this->_getPlaceholder($value);
        if (!$placeholder) {
            return $value;
        }

        $url = null;
        if ($placeholder === 'unsecure_base_url') {
            $url = $this->_getValue($this->urlPaths['unsecureBaseUrl'], $data);
        } elseif ($placeholder === 'secure_base_url') {
            $url = $this->_getValue($this->urlPaths['secureBaseUrl'], $data);
        }

        $originalValue = $value;

        if ($url) {
            $value = str_replace('{{' . $placeholder . '}}', $url, $value);
        } elseif (str_contains((string) $value, (string) $this->urlPlaceholder)) {
            $value = str_replace($this->urlPlaceholder, $this->request->getDistroBaseUrl(), $value);
        } else {
            $configPath = $placeholder === 'secure_base_url'
                ? $this->urlPaths['secureBaseUrl']
                : $this->urlPaths['unsecureBaseUrl'];
            throw new LocalizedException(
                __('Cannot resolve "{{%1}}" because "%2" is empty.', $placeholder, $configPath)
            );
        }

        // Only recurse when the value changed; otherwise unresolved placeholders loop forever.
        if ($value !== $originalValue && $this->_getPlaceholder($value) !== null) {
            $value = $this->_processPlaceholders($value, $data);
        }

        return $value;
    }

    /**
     * Get placeholder from value
     *
     * @param string $value
     * @return string|null
     */
    protected function _getPlaceholder($value)
    {
        if (!is_string($value) || $value === '') {
            return null;
        }
        if (preg_match('/{{(.*)}}.*/', $value, $matches)) {
            $placeholder = $matches[1];
            if ($placeholder === 'unsecure_base_url' ||
                $placeholder === 'secure_base_url' ||
                str_contains($value, (string) $this->urlPlaceholder)
            ) {
                return $placeholder;
            }
        }
        return null;
    }

    /**
     * Get array value by path
     *
     * @param string $path
     * @param array $data
     * @return array|string|null
     */
    protected function _getValue($path, array $data)
    {
        $keys = explode('/', (string)$path);
        foreach ($keys as $key) {
            if (is_array($data) && (isset($data[$key]) || array_key_exists($key, $data))) {
                $data = $data[$key];
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     * Set array value by path
     *
     * @deprecated 101.0.4 This method isn't used in process() implementation anymore
     * @see process()
     *
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     */
    protected function _setValue(array &$container, $path, $value)
    {
        $segments = explode('/', (string)$path);
        $currentPointer = &$container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = &$currentPointer[$segment];
        }
        $currentPointer = $value;
    }
}
