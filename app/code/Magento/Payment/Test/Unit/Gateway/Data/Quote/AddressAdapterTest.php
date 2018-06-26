<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data\Quote;

use Magento\Payment\Gateway\Data\Quote\AddressAdapter;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class AddressAdapterTest
 */
class AddressAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressAdapter */
    protected $model;

    /**
     * @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    protected function setUp()
    {
        $this->quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();

        $this->model = new AddressAdapter($this->quoteAddressMock);
    }

    public function testGetRegion()
    {
        $expected = 'California';
        $this->quoteAddressMock->expects($this->once())->method('getRegionCode')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getRegionCode());
    }

    public function testGetCountryId()
    {
        $expected = '10';
        $this->quoteAddressMock->expects($this->once())->method('getCountryId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCountryId());
    }

    /**
     * @param $street array|null
     * @param $expected string
     * @dataProvider testStreetLine1DataProvider
     */
    public function testStreetLine1($street, $expected)
    {
        $this->quoteAddressMock->expects($this->once())->method('getStreet')->willReturn($street);
        $this->assertEquals($expected, $this->model->getStreetLine1());
    }

    /**
     * @return array
     */
    public function testStreetLine1DataProvider()
    {
        return [
            [['Street Line 1'], 'Street Line 1'], //$street, $expected
            [null, '']
        ];
    }

    /**
     * @param $street array|null
     * @param $expected string
     * @dataProvider testStreetLine2DataProvider
     */
    public function testStreetLine2($street, $expected)
    {
        $this->quoteAddressMock->expects($this->once())->method('getStreet')->willReturn($street);
        $this->assertEquals($expected, $this->model->getStreetLine2());
    }

    /**
     * @return array
     */
    public function testStreetLine2DataProvider()
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
        $this->quoteAddressMock->expects($this->once())->method('getTelephone')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getTelephone());
    }

    public function testGetPostcode()
    {
        $expected = '90232';
        $this->quoteAddressMock->expects($this->once())->method('getPostcode')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getPostcode());
    }

    public function testGetCity()
    {
        $expected = 'New York';
        $this->quoteAddressMock->expects($this->once())->method('getCity')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCity());
    }

    public function testGetFirstname()
    {
        $expected = 'John';
        $this->quoteAddressMock->expects($this->once())->method('getFirstname')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getFirstname());
    }

    public function testGetLastname()
    {
        $expected = 'Doe';
        $this->quoteAddressMock->expects($this->once())->method('getLastname')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getLastname());
    }

    public function testGetMiddlename()
    {
        $expected = 'Middlename';
        $this->quoteAddressMock->expects($this->once())->method('getMiddlename')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getMiddlename());
    }

    public function testGetCustomerId()
    {
        $expected = 1;
        $this->quoteAddressMock->expects($this->once())->method('getCustomerId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCustomerId());
    }

    public function testGetEmail()
    {
        $expected = 'test@gmail.com';
        $this->quoteAddressMock->expects($this->once())->method('getEmail')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getEmail());
    }
}
