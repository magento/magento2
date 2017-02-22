<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\Source\Product\Options;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Type
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOptionConfig;

    protected function setUp()
    {
        $this->productOptionConfig = $this->getMockBuilder('Magento\Catalog\Model\ProductOptions\ConfigInterface')
            ->setMethods(['getAll'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Catalog\Model\Config\Source\Product\Options\Type',
            ['productOptionConfig' => $this->productOptionConfig]
        );
    }

    public function testToOptionArray()
    {
        $allOptions = [
            [
                'types' => [
                    ['disabled' => false, 'label' => 'typeLabel', 'name' => 'typeName'],
                ],
                'label' => 'optionLabel',
            ],
            [
                'types' => [
                    ['disabled' => true],
                ],
                'label' => 'optionLabelDisabled'
            ],
        ];
        $expect = [
            ['value' => '', 'label' => __('-- Please select --')],
            [
                'label' => 'optionLabel',
                'optgroup-name' => 'optionLabel',
                'value' => [['label' => 'typeLabel', 'value' => 'typeName']]
            ],
        ];

        $this->productOptionConfig->expects($this->any())->method('getAll')->will($this->returnValue($allOptions));

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
