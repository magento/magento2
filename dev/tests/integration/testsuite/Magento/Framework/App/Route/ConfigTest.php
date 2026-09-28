<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Route;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;

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
     */
    #[DataProvider('getRouteFrontNameDataProvider')]
    public function testGetRouteFrontName($route, $scope)
    {
        self::assertEquals(
            Bootstrap::getObjectManager()->create(Config::class)->getRouteFrontName($route, $scope),
            Bootstrap::getObjectManager()->create(Config::class)->getRouteFrontName($route, $scope)
        );
    }

    public static function getRouteFrontNameDataProvider()
    {
        return [
            ['adminhtml', 'adminhtml'],
            ['catalog', 'frontend'],
        ];
    }
}
