<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weightSwitcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeFormat;

    protected function setUp()
    {
        $this->weightSwitcher = $this->getMock(
            'Magento\Framework\Data\Form\Element\Radios',
            ['setId', 'setName', 'setLabel', 'setForm'],
            [],
            '',
            false
        );
        $this->weightSwitcher->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setName')->will($this->returnSelf());
        $this->weightSwitcher->expects($this->any())->method('setLabel')->will($this->returnSelf());

        $this->factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $this->factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('radios')
        )->will(
            $this->returnValue($this->weightSwitcher)
        );
        $this->localeFormat = $this->getMock(
            '\Magento\Framework\Locale\Format',
            [],
            [],
            '',
            false
        );

        $this->collectionFactory = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->_model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight',
            [
                'factoryElement' => $this->factory,
                'factoryCollection' => $this->collectionFactory,
                'localeFormat' => $this->localeFormat
            ]
        );
    }

    public function testSetForm()
    {
        $form = $this->getMock('Magento\Framework\Data\Form', [], [], '', false);
        $this->weightSwitcher->expects(
            $this->any()
        )->method(
            'setForm'
        )->with(
            $this->equalTo($form)
        )->will(
            $this->returnSelf()
        );

        $this->_model->setForm($form);
    }

    public function testGetEscapedValue()
    {
        $this->localeFormat->expects(
            $this->any()
        )->method(
            'getPriceFormat'
        )->willReturn([
            'precision' => 2,
            'decimalSymbol' => ',',
            'groupSymbol' => '.',
        ]);

        $this->_model->setValue('30000.4');
        $this->_model->setEntityAttribute(true);

        $return = $this->_model->getEscapedValue('30000.4');
        $this->assertEquals('30.000,40', $return);
    }
}
