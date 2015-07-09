<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Block\Element\Weee;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight
     */
    protected $model;

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

        $factory = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);

        $this->model = $objectManager->getObject(
            'Magento\Weee\Block\Element\Weee\Tax',
            [
                'factoryElement' => $factory,
                'factoryCollection' => $collectionFactory,
                'localeFormat' => $localeFormat
            ]
        );

        $inputValue = [
            ['value' => '30000.4'],
        ];
        $this->model->setValue($inputValue);
        $this->model->setEntityAttribute(true);

        $return = $this->model->getEscapedValue();
        $this->assertEquals(
            [
                ['value' => '30.000,40'],
            ],
            $return
        );
    }
}
