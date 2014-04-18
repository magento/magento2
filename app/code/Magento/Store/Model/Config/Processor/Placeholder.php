<?php
/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\Model\Config\Processor;

class Placeholder
{
    /**
     * @var \Magento\Framework\App\RequestInterface
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string[] $urlPaths
     * @param string $urlPlaceholder
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
     */
    public function process(array $data = array())
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
                $distroBaseUrl = $this->request->getDistroBaseUrl();
                $value = str_replace($this->urlPlaceholder, $distroBaseUrl, $value);
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
     */
    protected function _getValue($path, array $data)
    {
        $keys = explode('/', $path);
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
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
     */
    protected function _setValue(array &$container, $path, $value)
    {
        $segments = explode('/', $path);
        $currentPointer =& $container;
        foreach ($segments as $segment) {
            if (!isset($currentPointer[$segment])) {
                $currentPointer[$segment] = array();
            }
            $currentPointer =& $currentPointer[$segment];
        }
        $currentPointer = $value;
    }
}
