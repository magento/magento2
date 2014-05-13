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
namespace Magento\Backend\Model\Config\Structure\Element;

class FlyweightFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\FlyweightFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\FlyweightFactory(
            $this->_objectManagerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValueMap(
                array(
                    array('Magento\Backend\Model\Config\Structure\Element\Section', array(), 'sectionObject'),
                    array('Magento\Backend\Model\Config\Structure\Element\Group', array(), 'groupObject'),
                    array('Magento\Backend\Model\Config\Structure\Element\Field', array(), 'fieldObject')
                )
            )
        );
        $this->assertEquals('sectionObject', $this->_model->create('section'));
        $this->assertEquals('groupObject', $this->_model->create('group'));
        $this->assertEquals('fieldObject', $this->_model->create('field'));
    }
}
