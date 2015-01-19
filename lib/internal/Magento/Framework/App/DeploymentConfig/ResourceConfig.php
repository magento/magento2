<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class ResourceConfig extends AbstractSegment
{
    /**
     * Array Key for connection
     */
    const KEY_CONNECTION = 'connection';

    /**
     * Segment key
     */
    const CONFIG_KEY = 'resource';

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        $this->data = [
            'default_setup' => [
                self::KEY_CONNECTION => 'default',
            ],
        ];
        if (!$this->validate($data)) {
            throw new \InvalidArgumentException('Invalid resource configuration.');
        }
        parent::__construct($this->update($data));
    }

    /**
     * Validate input data
     *
     * @param array $data
     * @return bool
     */
    private function validate(array $data = [])
    {
        foreach ($data as $resource) {
            if (!isset($resource[self::KEY_CONNECTION])) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return self::CONFIG_KEY;
    }
}
