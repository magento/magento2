<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\Order\AddressAdapter;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class AddressAdapterTest
 */
class AddressAdapterTest extends \PHPUnit\Framework\TestCase
{
    /** @var AddressAdapter */
    protected $model;

    /**
     * @var OrderAddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressMock;

    protected function setUp()
    {
        $this->orderAddressMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)
            ->getMockForAbstractClass();

        $this->model = new AddressAdapter($this->orderAddressMock);
    }

    public function testGetRegion()
    {
        $expected = 'California';
        $this->orderAddressMock->expects($this->once())->method('getRegionCode')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getRegionCode());
    }

    public function testGetCountryId()
    {
        $expected = '10';
        $this->orderAddressMock->expects($this->once())->method('getCountryId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCountryId());
    }

    /**
     * @param $street array|null
     * @param $expected string
     * @dataProvider streetLine1DataProvider
     */
    public function testStreetLine1($street, $expected)
    {
        $this->orderAddressMock->expects($this->once())->method('getStreet')->willReturn($street);
        $this->assertEquals($expected, $this->model->getStreetLine1());
    }

    public function streetLine1DataProvider()
    {
        return [
            [['Street Line 1'], 'Street Line 1'], //$street, $expected
            [null, '']
        ];
    }

    /**
     * @param $street array|null
     * @param $expected string
     * @dataProvider streetLine2DataProvider
     */
    public function testStreetLine2($street, $expected)
    {
        $this->orderAddressMock->expects($this->once())->method('getStreet')->willReturn($street);
        $this->assertEquals($expected, $this->model->getStreetLine2());
    }

    public function streetLine2DataProvider()
    {
        return [
            [['Street Line 1', 'Street Line 2',], 'Street Line 2'], //$street, $expected
            [['Street Line 1'], ''],
            [null, '']
        ];
    }

    public function testGetTelephone()
    {
        $expected = '555-234-456';
        $this->orderAddressMock->expects($this->once())->method('getTelephone')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getTelephone());
    }

    public function testGetPostcode()
    {
        $expected = '90232';
        $this->orderAddressMock->expects($this->once())->method('getPostcode')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getPostcode());
    }

    public function testGetCity()
    {
        $expected = 'New York';
        $this->orderAddressMock->expects($this->once())->method('getCity')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCity());
    }

    public function testGetFirstname()
    {
        $expected = 'John';
        $this->orderAddressMock->expects($this->once())->method('getFirstname')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getFirstname());
    }

    public function testGetLastname()
    {
        $expected = 'Doe';
        $this->orderAddressMock->expects($this->once())->method('getLastname')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getLastname());
    }

    public function testGetMiddlename()
    {
        $expected = 'Middlename';
        $this->orderAddressMock->expects($this->once())->method('getMiddlename')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getMiddlename());
    }

    public function testGetCustomerId()
    {
        $expected = 1;
        $this->orderAddressMock->expects($this->once())->method('getCustomerId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCustomerId());
    }

    public function testGetEmail()
    {
        $expected = 'test@gmail.com';
        $this->orderAddressMock->expects($this->once())->method('getEmail')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getEmail());
    }
}
