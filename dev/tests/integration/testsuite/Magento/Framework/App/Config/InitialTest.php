<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Initial as Config;

class InitialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testGetMetadata()
    {
        $this->cleanCache();
        $this->assertEquals(
            $this->objectManager->create(Config::class)->getMetadata(),
            $this->objectManager->create(Config::class)->getMetadata()
        );
    }

    /**
     * @param string $scope
     * @dataProvider getDataDataProvider
     */
    public function testGetData($scope)
    {
        $this->cleanCache();
        $this->assertEquals(
            $this->objectManager->create(Config::class)->getData($scope),
            $this->objectManager->create(Config::class)->getData($scope)
        );
    }

    public function getDataDataProvider()
    {
        return [
            ['default'],
            ['stores|default'],
            ['websites|default']
        ];
    }

    private function cleanCache()
    {
        /** @var Pool $cachePool */
        $cachePool = $this->objectManager->get(Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
