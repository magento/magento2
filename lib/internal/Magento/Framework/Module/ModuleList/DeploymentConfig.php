<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

/**
 * Deployment configuration for modules
 */
class DeploymentConfig
{
    /**
     * Segment key
     */
    const KEY_MODULES = 'modules';

    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!preg_match('/^[A-Z][A-Za-z\d]+_[A-Z][A-Za-z\d]+$/', $key)) {
                throw new \InvalidArgumentException("Incorrect module name: '{$key}'");
            }
            $this->data[$key] = (int)$value;
        }
    }

    /**
     * Returns config data
     *
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns key
     *
     * @return string
     */
    public function getKey()
    {
        return self::KEY_MODULES;
    }
}
