<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Url;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Model\Url\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_model = new \Magento\DesignEditor\Model\Url\Factory($this->_objectManager);
    }

    public function testConstruct()
    {
        $this->assertAttributeInstanceOf('Magento\Framework\ObjectManagerInterface', '_objectManager', $this->_model);
    }

    public function testReplaceClassName()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'configure'
        )->with(
            ['preferences' => ['Magento\Framework\UrlInterface' => 'TestClass']]
        );

        $this->assertEquals($this->_model, $this->_model->replaceClassName('TestClass'));
    }

    public function testCreate()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\UrlInterface',
            []
        )->will(
            $this->returnValue('ModelInstance')
        );

        $this->assertEquals('ModelInstance', $this->_model->create());
    }
}
