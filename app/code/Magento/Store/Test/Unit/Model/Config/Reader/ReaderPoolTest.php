<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Store\Test\Unit\Model\Config\Reader;

class ReaderPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Reader\ReaderPool
     */
    protected $_model;

    /**
     * @var \Magento\Store\Model\Config\Reader\DefaultReader
     */
    protected $_defaultReaderMock;

    /**
     * @var \Magento\Store\Model\Config\Reader\Website
     */
    protected $_websiteReaderMock;

    /**
     * @var \Magento\Store\Model\Config\Reader\Store
     */
    protected $_storeReaderMock;

    protected function setUp()
    {
        $this->_defaultReaderMock = $this->getMock(
            \Magento\Store\Model\Config\Reader\DefaultReader::class, [], [], '', false
        );
        $this->_websiteReaderMock = $this->getMock(
            \Magento\Store\Model\Config\Reader\Website::class, [], [], '', false
        );
        $this->_storeReaderMock = $this->getMock(
            \Magento\Store\Model\Config\Reader\Store::class, [], [], '', false
        );

        $this->_model = new \Magento\Store\Model\Config\Reader\ReaderPool([
            'default' => $this->_defaultReaderMock,
            'website' => $this->_websiteReaderMock,
            'store' => $this->_storeReaderMock,
        ]);
    }

    /**
     * @covers \Magento\Store\Model\Config\Reader\ReaderPool::getReader
     * @dataProvider getReaderDataProvider
     * @param string $scope
     * @param string $instanceType
     */
    public function testGetReader($scope, $instanceType)
    {
        $this->assertInstanceOf($instanceType, $this->_model->getReader($scope));
    }

    /**
     * @return array
     */
    public function getReaderDataProvider()
    {
        return [
            [
                'scope' => 'default',
                'expectedResult' => \Magento\Store\Model\Config\Reader\DefaultReader::class,
            ],
            [
                'scope' => 'website',
                'expectedResult' => \Magento\Store\Model\Config\Reader\Website::class
            ],
            [
                'scope' => 'store',
                'expectedResult' => \Magento\Store\Model\Config\Reader\Store::class
            ],
        ];
    }
}
