<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Setup\Model\ConfigOptionsList;

class EncryptConfig extends AbstractSegment
{
    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        if (!isset($data[ConfigOptionsList::KEY_ENCRYPTION_KEY])) {
            throw new \InvalidArgumentException('No encryption key provided');
        }
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return ConfigOptionsList::ENCRYPT_CONFIG_KEY;
    }
}
