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

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure\Element\Group
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cloneFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_depMapperMock;

    protected function setUp()
    {
        $this->_iteratorMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Iterator\Field',
            array(),
            array(),
            '',
            false
        );
        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $this->_cloneFactoryMock = $this->getMock(
            'Magento\Backend\Model\Config\BackendClone\Factory',
            array(),
            array(),
            '',
            false
        );
        $this->_depMapperMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper',
            array(),
            array(),
            '',
            false
        );

        $this->_model = new \Magento\Backend\Model\Config\Structure\Element\Group(
            $this->_storeManagerMock,
            $this->_iteratorMock,
            $this->_cloneFactoryMock,
            $this->_depMapperMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_iteratorMock);
        unset($this->_storeManagerMock);
        unset($this->_cloneFactoryMock);
        unset($this->_depMapperMock);
    }

    public function testShouldCloneFields()
    {
        $this->assertFalse($this->_model->shouldCloneFields());
        $this->_model->setData(array('clone_fields' => 1), 'scope');
        $this->assertTrue($this->_model->shouldCloneFields());
        $this->_model->setData(array('clone_fields' => 0), 'scope');
        $this->assertFalse($this->_model->shouldCloneFields());
        $this->_model->setData(array('clone_fields' => false), 'scope');
        $this->assertFalse($this->_model->shouldCloneFields());
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testGetCloneModelThrowsExceptionIfNoSourceModelIsSet()
    {
        $this->_model->getCloneModel();
    }

    public function testGetCloneModelCreatesCloneModel()
    {
        $cloneModel = $this->getMock('Magento\Framework\App\Config\ValueInterface', array(), array(), '', false);
        $this->_depMapperMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Dependency\Mapper',
            array(),
            array(),
            '',
            false
        );
        $this->_cloneFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'clone_model_name'
        )->will(
            $this->returnValue($cloneModel)
        );
        $this->_model->setData(array('clone_model' => 'clone_model_name'), 'scope');
        $this->assertEquals($cloneModel, $this->_model->getCloneModel());
    }

    public function testGetFieldsetSetsOnlyNonArrayValuesToFieldset()
    {
        $fieldsetMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Fieldset',
            array('setOriginalData'),
            array(),
            '',
            false
        );
        $fieldsetMock->expects(
            $this->once()
        )->method(
            'setOriginalData'
        )->with(
            array('var1' => 'val1', 'var2' => 'val2')
        );

        $this->_model->setData(array('var1' => 'val1', 'var2' => 'val2', 'var3' => array('val3')), 'scope');
        $this->_model->populateFieldset($fieldsetMock);
    }

    public function testIsExpanded()
    {
        $this->assertFalse($this->_model->isExpanded());
        $this->_model->setData(array('expanded' => 1), 'scope');
        $this->assertTrue($this->_model->isExpanded());
        $this->_model->setData(array('expanded' => 0), 'scope');
        $this->assertFalse($this->_model->isExpanded());
        $this->_model->setData(array('expanded' => null), 'scope');
        $this->assertFalse($this->_model->isExpanded());
    }

    public function testGetFieldsetCss()
    {
        $this->assertEquals('', $this->_model->getFieldsetCss());
        $this->_model->setData(array('fieldset_css' => 'some_css'), 'scope');
        $this->assertEquals('some_css', $this->_model->getFieldsetCss());
    }

    public function testGetDependenciesWithoutDependencies()
    {
        $this->_depMapperMock->expects($this->never())->method('getDependencies');
    }

    public function testGetDependenciesWithDependencies()
    {
        $fields = array(
            'field_4' => array(
                'id' => 'section_2/group_3/field_4',
                'value' => 'someValue',
                'dependPath' => array('section_2', 'group_3', 'field_4')
            )
        );
        $this->_model->setData(array('depends' => array('fields' => $fields)), 0);
        $this->_depMapperMock->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->with(
            $fields,
            'test_scope'
        )->will(
            $this->returnArgument(0)
        );

        $this->assertEquals($fields, $this->_model->getDependencies('test_scope'));
    }
}
