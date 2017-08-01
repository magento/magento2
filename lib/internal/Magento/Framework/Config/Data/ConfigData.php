<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Data;

/**
 * Data transfer object to store config data for config options
 * @api
 * @since 2.0.0
 */
class ConfigData
{
    /**
     * File key
     *
     * @var string
     * @since 2.0.0
     */
    private $fileKey;

    /**
     * Data
     *
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * Override previous config options when save
     *
     * @var bool
     * @since 2.1.0
     */
    private $overrideWhenSave = false;

    /**
     * Constructor
     *
     * @param string $fileKey
     * @since 2.0.0
     */
    public function __construct($fileKey)
    {
        $this->fileKey = $fileKey;
    }

    /**
     * Gets File Key
     *
     * @return string
     * @since 2.0.0
     */
    public function getFileKey()
    {
        return $this->fileKey;
    }

    /**
     * Gets Data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets override when save flag
     *
     * @param bool $overrideWhenSave
     * @return void
     * @since 2.1.0
     */
    public function setOverrideWhenSave($overrideWhenSave)
    {
        $this->overrideWhenSave = $overrideWhenSave;
    }

    /**
     * Gets override when save flag
     *
     * @return bool
     * @since 2.1.0
     */
    public function isOverrideWhenSave()
    {
        return $this->overrideWhenSave;
    }

    /**
     * Updates a value in ConfigData configuration by specified path
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function set($path, $value)
    {
        $chunks = $this->expand($path);
        $data = [];
        $element = &$data;

        while ($chunks) {
            $key = array_shift($chunks);
            if ($chunks) {
                $element[$key] = [];
                $element = &$element[$key];
            } else {
                $element[$key] = $value;
            }
        }

        $this->data = array_replace_recursive($this->data, $data);
    }

    /**
     * Expands a path into chunks
     *
     * All chunks must be not empty and there must be at least two.
     *
     * @param string $path
     * @return string[]
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    private function expand($path)
    {
        $chunks = explode('/', $path);

        foreach ($chunks as $chunk) {
            if ('' == $chunk) {
                throw new \InvalidArgumentException(
                    "Path '$path' is invalid. It cannot be empty nor start or end with '/'"
                );
            }
        }

        return $chunks;
    }
}
