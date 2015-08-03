<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

class WeightTest extends \PHPUnit_Framework_TestCase
{
    const VIRTUAL_FIELD_HTML_ID = 'weight_and_type_switcher';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Data\Form\Element\Checkbox
     */
    protected $_virtual;

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

        $helper = $this->getMock(
            'Magento\Catalog\Helper\Product',
            ['getTypeSwitcherControlLabel'],
            [],
            '',
            false,
            false
        );
        $helper->expects(
            $this->any()
        )->method(
            'getTypeSwitcherControlLabel'
        )->will(
            $this->returnValue('Virtual / Downloadable')
        );

        $this->_virtual = $this->getMock(
            'Magento\Framework\Data\Form\Element\Checkbox',
            ['setId', 'setName', 'setLabel', 'setForm'],
            [],
            '',
            false,
            false
        );
        $this->_virtual->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->_virtual->expects($this->any())->method('setName')->will($this->returnSelf());
        $this->_virtual->expects($this->any())->method('setLabel')->will($this->returnSelf());
        $this->_virtual->expects(
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
            $this->equalTo('checkbox')
        )->will(
            $this->returnValue($this->_virtual)
        );

        $this->_model = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            ['factoryElement' => $factory, 'factoryCollection' => $collectionFactory, 'helper' => $helper]
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

        $helper = $this->getMock(
            'Magento\Catalog\Helper\Product',
            ['getTypeSwitcherControlLabel'],
            [],
            '',
            false,
            false
        );
        $helper->expects(
            $this->any()
        )->method(
            'getTypeSwitcherControlLabel'
        )->will(
            $this->returnValue('Virtual / Downloadable')
        );

        $this->_virtual = $this->getMock(
            'Magento\Framework\Data\Form\Element\Checkbox',
            ['setId', 'setName', 'setLabel'],
            [],
            '',
            false,
            false
        );
        $this->_virtual->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->_virtual->expects($this->any())->method('setName')->will($this->returnSelf());
        $this->_virtual->expects($this->any())->method('setLabel')->will($this->returnSelf());

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('checkbox')
        )->will(
            $this->returnValue($this->_virtual)
        );

        $this->_model = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            [
                'factoryElement' => $factory,
                'factoryCollection' => $collectionFactory,
                'helper' => $helper,
                'localeFormat' => $localeFormat
            ]
        );

        $this->_model->setValue('30000.4');
        $this->_model->setEntityAttribute(true);

        $return = $this->_model->getEscapedValue('30000.4');
        $this->assertEquals('30.000,40', $return);
    }
}
