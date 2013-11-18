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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config\Data;

class BackendModelPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Data\BackendModelPoolTest
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_model = new \Magento\Core\Model\Config\Data\BackendModelPool($this->_objectManager);
    }

    /**
     * @covers \Magento\Core\Model\Config\Data\BackendModelPool::get
     */
    public function testGetModelWithCorrectInterface()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Config\Data\TestBackendModel')
            ->will($this->returnValue(new \Magento\Core\Model\Config\Data\TestBackendModel()));

        $this->assertInstanceOf('Magento\Core\Model\Config\Data\TestBackendModel',
            $this->_model->get('Magento\Core\Model\Config\Data\TestBackendModel'));
    }

    /**
     * @covers \Magento\Core\Model\Config\Data\BackendModelPool::get
     * @expectedException \InvalidArgumentException
     */
    public function testGetModelWithWrongInterface()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Config\Data\WrongBackendModel')
            ->will($this->returnValue(new \Magento\Core\Model\Config\Data\WrongBackendModel()));

        $this->_model->get('Magento\Core\Model\Config\Data\WrongBackendModel');
    }

    /**
     * @covers \Magento\Core\Model\Config\Data\BackendModelPool::get
     */
    public function testGetMemoryCache()
    {
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Core\Model\Config\Data\TestBackendModel')
            ->will($this->returnValue(new \Magento\Core\Model\Config\Data\TestBackendModel()));

        $this->_model->get('Magento\Core\Model\Config\Data\TestBackendModel');
        $this->_model->get('Magento\Core\Model\Config\Data\TestBackendModel');
    }
}
