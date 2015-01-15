<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class BackendConfig extends AbstractSegment
{
    /**
     * Array key for backend front name
     */
    const KEY_FRONTNAME = 'frontName';

    /**
     * Segment key
     */
    const CONFIG_KEY = 'backend';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        if (empty($data[self::KEY_FRONTNAME])) {
            throw new \InvalidArgumentException("No backend frontname provided.");
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data[self::KEY_FRONTNAME])) {
            throw new \InvalidArgumentException("Invalid backend frontname {$data[self::KEY_FRONTNAME]}");
        }
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }
}
