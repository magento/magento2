<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory
     */
    protected $_readerFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_readerMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock(
            'Magento\Framework\ObjectManager\Config\Reader\Dom',
            [],
            [],
            '',
            false
        );

        $this->_readerFactoryMock = $this->getMock(
            'Magento\Framework\ObjectManager\Config\Reader\DomFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_readerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_readerMock)
        );

        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Config', [], [], '', false);
        $this->_model = new \Magento\Framework\App\ObjectManager\ConfigLoader(
            $this->_cacheMock, $this->_readerFactoryMock
        );
    }

    /**
     * @param $area
     * @dataProvider loadDataProvider
     */
    public function testLoad($area)
    {
        $configData = ['some' => 'config', 'data' => 'value'];

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $area . '::DiConfig'
        )->will(
            $this->returnValue(false)
        );

        $this->_readerMock->expects($this->once())->method('read')->with($area)->will($this->returnValue($configData));

        $this->assertEquals($configData, $this->_model->load($area));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            'global files' => ['global'],
            'adminhtml files' => ['adminhtml'],
            'any area files' => ['any']
        ];
    }
}
