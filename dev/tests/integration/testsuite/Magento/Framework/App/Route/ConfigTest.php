<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
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
        CacheCleaner::cleanAll();
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
}
