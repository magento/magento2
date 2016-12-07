<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

use Magento\TestFramework\Helper\CacheCleaner;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    private $object;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->object = $objectManager->create(
            \Magento\Framework\App\ObjectManager\ConfigLoader::class
        );
    }

    public function testLoad()
    {
        CacheCleaner::cleanAll();
        $data = $this->object->load('global');
        $this->assertNotEmpty($data);
        $cachedData = $this->object->load('global');
        $this->assertEquals($data, $cachedData);
    }
}
