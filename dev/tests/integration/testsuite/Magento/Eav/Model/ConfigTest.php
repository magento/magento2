<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(Config::class);
    }

    public function testGetEntityAttributeCodes()
    {
        $entityType = 'catalog_product';
        CacheCleaner::cleanAll();
        $this->assertEquals(
            $this->config->getEntityAttributeCodes($entityType),
            $this->config->getEntityAttributeCodes($entityType)
        );
    }
}
