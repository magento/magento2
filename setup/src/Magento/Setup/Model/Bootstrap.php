<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\Bootstrap as MagentoAppBootstrap;

/**
 * Class Bootstrap
 * @since 2.2.0
 */
class Bootstrap
{
    /**
     * Creates instance of object manager factory
     *
     * @param string $rootDir
     * @param array $initParams
     * @return ObjectManagerFactory
     * @since 2.2.0
     */
    public function createObjectManagerFactory($rootDir, array $initParams)
    {
        return MagentoAppBootstrap::createObjectManagerFactory($rootDir, $initParams);
    }
}
