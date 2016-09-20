<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Route\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config1;

    /**
     * @var Config
     */
    private $config2;

    protected function setUp()
    {
        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var Pool $cachePool */
        $cachePool = $objectManager->get(Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }

        $objectManager->removeSharedInstance(Config::class);
        $this->config1 = $objectManager->get(Config::class);

        $objectManager->removeSharedInstance(Config::class);
        $this->config2 = $objectManager->get(Config::class);
    }

    /**
     * @param string $route
     * @param string $scope
     * @dataProvider getRouteFrontNameDataProvider
     */
    public function testGetRouteFrontName($route, $scope)
    {
        $this->assertEquals(
            $this->config1->getRouteFrontName($route, $scope),
            $this->config2->getRouteFrontName($route, $scope)
        );
    }

    public function getRouteFrontNameDataProvider()
    {
        return [
            ['adminhtml', 'adminhtml'],
            ['catalog', 'frontend'],
        ];
    }
}
