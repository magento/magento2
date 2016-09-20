<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Config\Initial as Config;

class InitialTest extends \PHPUnit_Framework_TestCase
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

    public function testGetMetadata()
    {
        $this->assertEquals($this->config1->getMetadata(), $this->config2->getMetadata());
    }

    /**
     * @param string $scope
     * @dataProvider getDataDataProvider
     */
    public function testGetData($scope)
    {
        $this->assertEquals($this->config1->getData($scope), $this->config2->getData($scope));
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
