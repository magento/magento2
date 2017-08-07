<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 * @since 2.1.3
 */
class Placeholder
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.1.3
     */
    protected $request;

    /**
     * @var string[]
     * @since 2.1.3
     */
    protected $urlPaths;

    /**
     * @var string
     * @since 2.1.3
     */
    protected $urlPlaceholder;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string[] $urlPaths
     * @param string $urlPlaceholder
     * @since 2.1.3
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request, $urlPaths, $urlPlaceholder)
    {
        $this->request = $request;
        $this->urlPaths = $urlPaths;
        $this->urlPlaceholder = $urlPlaceholder;
    }

    /**
     * Replace placeholders with config values
     *
     * @param array $data
     * @return array
     * @since 2.1.3
     */
    public function process(array $data = [])
    {
        foreach (array_keys($data) as $key) {
            $this->_processData($data, $key);
        }
        return $data;
    }

    /**
     * Process array data recursively
     *
     * @param array &$data
     * @param string $path
     * @return void
     * @since 2.1.3
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
     * @since 2.1.3
     */
    protected function _processPlaceholders($value, $data)
    {
        $placeholder = $this->_getPlaceholder($value);
        if ($placeholder) {
            $url = false;
            if ($placeholder == 'unsecure_base_url') {
                $url = $this->_getValue($this->urlPaths['unsecureBaseUrl'], $data);
            } elseif ($placeholder == 'secure_base_url') {
                $url = $this->_getValue($this->urlPaths['secureBaseUrl'], $data);
            }

            if ($url) {
                $value = str_replace('{{' . $placeholder . '}}', $url, $value);
            } elseif (strpos($value, $this->urlPlaceholder) !== false) {
                // localhost is replaced for cli requests, for http requests method getDistroBaseUrl is used
                $value = str_replace($this->urlPlaceholder, 'http://localhost/', $value);
            }

            if (null !== $this->_getPlaceholder($value)) {
                $value = $this->_processPlaceholders($value, $data);
            }
        }
        return $value;
    }

    /**
     * Get placeholder from value
     *
     * @param string $value
     * @return string|null
     * @since 2.1.3
     */
    protected function _getPlaceholder($value)
    {
        if (is_string($value) && preg_match('/{{(.*)}}.*/', $value, $matches)) {
            $placeholder = $matches[1];
            if ($placeholder == 'unsecure_base_url' || $placeholder == 'secure_base_url' || strpos(
                $value,
                $this->urlPlaceholder
            ) !== false
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
     * @return array|null
     * @since 2.1.3
     */
    protected function _getValue($path, array $data)
    {
        $keys = explode('/', $path);
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
     * @param array &$container
     * @param string $path
     * @param string $value
     * @return void
     * @since 2.1.3
     */
    protected function _setValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer = & $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = [];
            }
            $currentPointer = & $currentPointer[$segment];
        }
        $currentPointer = $value;
    }
}
