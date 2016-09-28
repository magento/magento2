<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    private $object;

    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->object = $this->objectManager->create(
            \Magento\Framework\App\ObjectManager\ConfigLoader::class
        );
    }

    public function testLoad()
    {
        $this->cleanAllCache();
        $data = $this->object->load('global');
        $this->assertNotEmpty($data);
        $cachedData = $this->object->load('global');
        $this->assertEquals($data, $cachedData);
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
