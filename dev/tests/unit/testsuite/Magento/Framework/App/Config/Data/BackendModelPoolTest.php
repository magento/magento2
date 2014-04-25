<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Config\Data;

class BackendModelPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorFactory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\Data\ProcessorInterface
     */
    protected $_processorMock;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Framework\App\Config\Data\ProcessorFactory($this->_objectManager);
        $this->_processorMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\Data\ProcessorInterface');
        $this->_processorMock->expects($this->any())->method('processValue')->will($this->returnArgument(0));
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     */
    public function testGetModelWithCorrectInterface()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\TestBackendModel'
        )->will(
            $this->returnValue($this->_processorMock)
        );

        $this->assertInstanceOf(
            'Magento\Framework\App\Config\Data\ProcessorInterface',
            $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel')
        );
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     * @expectedException \InvalidArgumentException
     */
    public function testGetModelWithWrongInterface()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\WrongBackendModel'
        )->will(
            $this->returnValue(
                $this->getMock('Magento\Framework\App\Config\Data\WrongBackendModel', array(), array(), '', false)
            )
        );

        $this->_model->get('Magento\Framework\App\Config\Data\WrongBackendModel');
    }

    /**
     * @covers \Magento\Framework\App\Config\Data\ProcessorFactory::get
     */
    public function testGetMemoryCache()
    {
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Config\Data\TestBackendModel'
        )->will(
            $this->returnValue($this->_processorMock)
        );

        $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel');
        $this->_model->get('Magento\Framework\App\Config\Data\TestBackendModel');
    }
}
