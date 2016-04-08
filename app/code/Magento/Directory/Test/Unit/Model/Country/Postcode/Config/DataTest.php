<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder(
            'Magento\Directory\Model\Country\Postcode\Config\Reader'
        )->disableOriginalConstructor()->getMock();
        $this->cacheMock = $this->getMockBuilder(
            'Magento\Framework\App\Cache\Type\Config'
        )->disableOriginalConstructor()->getMock();
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->cacheMock->expects($this->any())->method('load')->will($this->returnValue(serialize($expected)));
        $configData = new \Magento\Directory\Model\Country\Postcode\Config\Data($this->readerMock, $this->cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
