<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Framework\Serialize\SerializerInterface;

class CountryofmanufactureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $cacheConfig;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture */
    private $countryOfManufacture;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->cacheConfig = $this->getMock(\Magento\Framework\App\Cache\Type\Config::class, [], [], '', false);
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->countryOfManufacture = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class,
            [
                'storeManager' => $this->storeManagerMock,
                'configCacheType' => $this->cacheConfig,
            ]
        );

        $this->serializerMock = $this->getMock(SerializerInterface::class, [], [], '', false);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->countryOfManufacture,
            'serializer',
            $this->serializerMock
        );
    }

    /**
     * Test for getAllOptions method
     *
     * @param $cachedDataSrl
     * @param $cachedDataUnsrl
     *
     * @dataProvider testGetAllOptionsDataProvider
     */
    public function testGetAllOptions($cachedDataSrl, $cachedDataUnsrl)
    {
        $this->storeMock->expects($this->once())->method('getCode')->will($this->returnValue('store_code'));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->cacheConfig->expects($this->once())
            ->method('load')
            ->with($this->equalTo('COUNTRYOFMANUFACTURE_SELECT_STORE_store_code'))
            ->will($this->returnValue($cachedDataSrl));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($cachedDataUnsrl);
        $this->assertEquals($cachedDataUnsrl, $this->countryOfManufacture->getAllOptions());
    }

    /**
     * Data provider for testGetAllOptions
     *
     * @return array
     */
    public function testGetAllOptionsDataProvider()
    {
        return
            [
                ['cachedDataSrl' => json_encode(['key' => 'data']), 'cachedDataUnsrl' => ['key' => 'data']]
            ];
    }
}
