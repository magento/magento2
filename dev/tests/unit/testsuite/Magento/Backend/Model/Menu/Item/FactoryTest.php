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
 * @category    Magento
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model\Menu\Item;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Item\Factory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * Constructor params
     *
     * @var array
     */
    protected $_params = array();

    protected function setUp()
    {
        $this->_objectFactoryMock = $this->getMock('Magento\ObjectManager');
        $this->_helperFactoryMock = $this->getMock('Magento\Core\Model\Factory\Helper', array(), array(), '', false);
        $this->_helperFactoryMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(array(
                array('Magento\Backend\Helper\Data', array(), 'backend_helper'),
                array('Magento\User\Helper\Data', array(), 'user_helper')
            )));

        $this->_model = new \Magento\Backend\Model\Menu\Item\Factory($this->_objectFactoryMock,
            $this->_helperFactoryMock);
    }

    public function testCreate()
    {
        $this->_objectFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('Magento\Backend\Model\Menu\Item'),
                $this->equalTo(array(
                    'helper' => 'user_helper',
                    'data' => array(
                        'title' => 'item1',
                        'dependsOnModule' => 'Magento\User\Helper\Data',
                    )
                ))
            );
        $this->_model->create(array(
            'module' => 'Magento\User\Helper\Data',
            'title' => 'item1',
            'dependsOnModule' => 'Magento\User\Helper\Data'
        ));
    }

    public function testCreateProvidesDefaultHelper()
    {
        $this->_objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('Magento\Backend\Model\Menu\Item'),
                $this->equalTo(array(
                    'helper' => 'backend_helper',
                    'data' => array()
                ))
        );
        $this->_model->create(array());
    }
}
