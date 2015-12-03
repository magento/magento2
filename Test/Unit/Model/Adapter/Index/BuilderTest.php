<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeRepository;

    /**
     * @var StoreConfigManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeConfig;

    /**
     * @var EsConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $esConfig;

    /**
     * Setup method
     * @return void
     */
    public function setUp()
    {
        $this->storeRepository = $this->getMockBuilder('Magento\Store\Api\StoreRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeConfig = $this->getMockBuilder('Magento\Store\Api\StoreConfigManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->esConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Index\Builder',
            [
                'storeRepository' => $this->storeRepository,
                'storeConfig' => $this->storeConfig,
                'esConfig' => $this->esConfig
            ]
        );
    }

    /**
     * Test build() method
     * 
     * @param string $locale
     * @dataProvider buildDataProvider
     */
    public function testBuild($locale)
    {
        $store = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $storeConfig = $this->getMockBuilder('Magento\Store\Api\Data\StoreConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepository->expects($this->once())
            ->method('getById')
            ->willReturn($store);
        $store->expects($this->once())
            ->method('getCode')
            ->willReturn('code');
        $this->storeConfig->expects($this->once())
            ->method('getStoreConfigs')
            ->willReturn([$storeConfig]);
        $storeConfig->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->esConfig->expects($this->once())
            ->method('getStemmerInfo')
            ->willReturn([
                'type' => 'stemmer',
                'default' => 'english',
                'en_US' => 'english',
            ]);

        $this->model->build();
    }

    /**
     * Test setStoreId() method
     */
    public function testSetStoreId()
    {
        $this->model->setStoreId(1);
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['en_US'],
            ['de_DE'],
        ];
    }
}
