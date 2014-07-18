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
namespace Magento\Framework\Data;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryElementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $elementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $allElementsMock;
    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    protected function setUp()
    {
        $this->_factoryElementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Factory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_factoryCollectionMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->allElementsMock =
            $this->getMock('Magento\Framework\Data\Form\Element\Collection', [], [], '', false);
        $this->_formKeyMock = $this->getMock(
            'Magento\Framework\Data\Form\FormKey',
            array('getFormKey'),
            array(),
            '',
            false
        );
        $methods = ['getId', 'setValue', 'setName', 'getName', '_wakeup'];
        $this->elementMock =
            $this->getMock('Magento\Framework\Data\Form\Element\AbstractElement', $methods, [], '', false);

        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
    }

    public function testFormKeyUsing()
    {
        $this->_factoryCollectionMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue(array()));
        $formKey = 'form-key';
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->will($this->returnValue($formKey));

        $this->_form->setUseContainer(true);
        $this->_form->setMethod('post');
        $this->assertContains($formKey, $this->_form->toHtml());
    }

    public function testAddValue()
    {
        $this->_factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
        $values = [
            'element_id' => 'value_one'
        ];
        $this->elementMock->expects($this->once())->method('getId')->will($this->returnValue('element_id'));
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock);
        $this->elementMock->expects($this->once())->method('setValue')->with('value_one');
        $this->_form->addElementToCollection($this->elementMock);
        $this->_form->addValues($values);
    }

    /**
     * @param int $number
     * @param string $elementId
     * @param string|null $value
     *
     * @dataProvider setValueDataProvider
     */
    public function testSetValue($number, $elementId, $value)
    {
        $this->_factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(array($this->elementMock)));
        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
        $values = [
            'element_two' => 'value_two'
        ];
        $this->elementMock->expects($this->exactly($number))->method('getId')->will($this->returnValue($elementId));
        $this->elementMock->expects($this->once())->method('setValue')->with($value);
        $this->_form->setValues($values);
    }

    public function setValueDataProvider()
    {
        return [
            'value_exists' => [2, 'element_two', 'value_two'],
            'value_not_exist' => [1, 'element_one', null]
        ];
    }

    public function testAddFieldNameSuffix()
    {
        $this->_factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue(array($this->elementMock)));
        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
        $this->elementMock->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $this->elementMock->expects($this->once())->method('setName')->with('_suffix[name]');
        $this->_form->addFieldNameSuffix('_suffix');
    }

    public function testAddEllement()
    {
        $this->_factoryCollectionMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
        $this->elementMock->expects($this->any())->method('getId')->will($this->returnValue('element_id'));
        $this->allElementsMock->expects($this->exactly(2))->method('add')->with($this->elementMock, false);
        $this->_form->addElement($this->elementMock);
    }
}
