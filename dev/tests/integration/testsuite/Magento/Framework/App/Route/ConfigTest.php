<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Route\Config;
use Magento\TestFramework\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @param string $route
     * @param string $scope
     * @dataProvider getRouteFrontNameDataProvider
     */
    public function testGetRouteFrontName($route, $scope)
    {
        $this->cleanCache();
        $this->assertEquals(
            $this->objectManager->create(Config::class)->getRouteFrontName($route, $scope),
            $this->objectManager->create(Config::class)->getRouteFrontName($route, $scope)
        );
    }

    public function getRouteFrontNameDataProvider()
    {
        return [
            ['adminhtml', 'adminhtml'],
            ['catalog', 'frontend'],
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
