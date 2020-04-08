<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Radios;
use Magento\Framework\Locale\Format;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WeightTest extends TestCase
{
    /**
     * @var Weight
     */
    protected $_model;

    /**
     * @var Radios|MockObject
     */
    protected $weightSwitcher;

    /**
     * @var Factory|MockObject
     */
    protected $factory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Format|MockObject
     */
    protected $localeFormat;

    protected function setUp()
    {
        $this->weightSwitcher = $this->createPartialMock(
            Radios::class,
            ['setId', 'setName', 'setLabel', 'setForm']
        );
        $this->weightSwitcher->method('setId')->will($this->returnSelf());
        $this->weightSwitcher->method('setName')->will($this->returnSelf());
        $this->weightSwitcher->method('setLabel')->will($this->returnSelf());

        $this->factory = $this->createMock(Factory::class);
        $this->factory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('radios')
        )->willReturn(
            $this->weightSwitcher
        );
        $this->localeFormat = $this->createMock(Format::class);

        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->_model = (new ObjectManager($this))->getObject(
            Weight::class,
            [
                'factoryElement' => $this->factory,
                'factoryCollection' => $this->collectionFactory,
                'localeFormat' => $this->localeFormat
            ]
        );
    }

    public function testSetForm()
    {
        $form = $this->createMock(Form::class);
        $this->weightSwitcher->method(
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
        $this->localeFormat->method(
            'getPriceFormat'
        )->willReturn([
            'precision' => 2,
            'decimalSymbol' => ',',
            'groupSymbol' => '.',
        ]);

        $this->_model->setValue(30000.4);
        $this->_model->setEntityAttribute(true);

        $return = $this->_model->getEscapedValue('30000.4');
        $this->assertEquals('30.000,40', $return);
    }
}
