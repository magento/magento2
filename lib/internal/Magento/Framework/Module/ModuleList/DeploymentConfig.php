<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

use Magento\Framework\App\DeploymentConfig\AbstractSegment;

/**
 * Deployment configuration segment for modules
 */
class DeploymentConfig extends AbstractSegment
{
    /**
     * Segment key
     */
    const CONFIG_KEY = 'modules';

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
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }
}
