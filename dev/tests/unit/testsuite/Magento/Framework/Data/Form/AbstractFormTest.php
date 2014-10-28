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

namespace Magento\Framework\Data\Form;


class AbstractFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryElementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryCollectionMock;


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

    /**
     * @var \Magento\Framework\Data\Form\AbstractForm
     */
    protected $abstractForm;

    protected function setUp()
    {
        $this->factoryElementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\Factory',
            array('create'),
            array(),
            '',
            false
        );
        $this->factoryCollectionMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->allElementsMock =
            $this->getMock('Magento\Framework\Data\Form\Element\Collection', [], [], '', false);
        $this->elementMock =
            $this->getMock('Magento\Framework\Data\Form\Element\AbstractElement', [], [], '', false);

        $this->abstractForm = new AbstractForm($this->factoryElementMock, $this->factoryCollectionMock, array());
    }

    public function testAddElement()
    {
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->elementMock->expects($this->once())->method('setForm');
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addElement($this->elementMock, false);
    }

    public function testAddField()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('hidden', ['data' => $config])
            ->will($this->returnValue($this->elementMock));
        $this->elementMock->expects($this->once())->method('setId')->with('store_type');
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->assertEquals($this->elementMock, $this->abstractForm->addField('store_type', 'hidden', $config));
        $this->abstractForm->removeField('hidden');
    }

    public function testAddFieldset()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('fieldset', ['data' => $config])
            ->will($this->returnValue($this->elementMock));
        $this->elementMock->expects($this->once())->method('setId')->with('hidden');
        $this->elementMock->expects($this->once())->method('setAdvanced')->with(false);
        $this->elementMock->expects($this->once())->method('setForm');
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addFieldset('hidden', $config);
    }

    public function testAddColumn()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('column', ['data' => $config])
            ->will($this->returnValue($this->elementMock));
        $this->elementMock->expects($this->once())->method('setId')->with('hidden');
        $this->elementMock->expects($this->exactly(2))->method('setForm')->will($this->returnSelf());
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->allElementsMock));
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addColumn('hidden', $config);
    }
}
