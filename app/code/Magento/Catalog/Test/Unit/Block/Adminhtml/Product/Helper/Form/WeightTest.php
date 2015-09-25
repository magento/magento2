<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

class WeightTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Data\Form\Element\Checkbox
     */
    protected $weightSwitcher;

    public function testSetForm()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);

        $collectionFactory = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $formKey = $this->getMock('Magento\Framework\Data\Form\FormKey', [], [], '', false);

        $form = new \Magento\Framework\Data\Form($factory, $collectionFactory, $formKey);

        $this->weightSwitcher = $this->getMock(
            'Magento\Framework\Data\Form\Element\Radios',
            ['setId', 'setName', 'setLabel', 'setForm'],
            [],
            '',
            false,
            false
        );
        $this->weightSwitcher->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setName')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setLabel')->will($this->returnSelf());
        $this->weightSwitcher->expects(
            $this->any()
        )->method(
            'setForm'
        )->with(
            $this->equalTo($form)
        )->will(
            $this->returnSelf()
        );

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('radios')
        )->will(
            $this->returnValue($this->weightSwitcher)
        );

        $this->_model = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            ['factoryElement' => $factory, 'factoryCollection' => $collectionFactory,]
        );

        $this->_model->setForm($form);
    }

    public function testGetEscapedValue()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $collectionFactory = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $localeFormat = $this->getMock(
            '\Magento\Framework\Locale\Format',
            [],
            [],
            '',
            false
        );
        $localeFormat->expects(
            $this->any()
        )->method(
            'getPriceFormat'
        )->willReturn([
            'precision' => 2,
            'decimalSymbol' => ',',
            'groupSymbol' => '.',
        ]);

        $this->weightSwitcher = $this->getMock(
            'Magento\Framework\Data\Form\Element\Radios',
            ['setId', 'setName', 'setLabel'],
            [],
            '',
            false,
            false
        );
        $this->weightSwitcher->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setName')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setLabel')->will($this->returnSelf());

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('radios')
        )->will(
            $this->returnValue($this->weightSwitcher)
        );

        $this->_model = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            [
                'factoryElement' => $factory,
                'factoryCollection' => $collectionFactory,
                'localeFormat' => $localeFormat
            ]
        );

        $this->_model->setValue('30000.4');
        $this->_model->setEntityAttribute(true);

        $return = $this->_model->getEscapedValue('30000.4');
        $this->assertEquals('30.000,40', $return);
    }
}
