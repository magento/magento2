<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Initial as Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;

class InitialTest extends \PHPUnit\Framework\TestCase
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
        CacheCleaner::cleanAll();
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
        CacheCleaner::cleanAll();
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
}
