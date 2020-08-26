<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\Source\Product\Options;

use Magento\Catalog\Model\Config\Source\Product\Options\Type;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    private $model;

    /**
     * @var ConfigInterface|MockObject
     */
    private $productOptionConfig;

    protected function setUp(): void
    {
        $this->productOptionConfig = $this->getMockBuilder(ConfigInterface::class)
            ->setMethods(['getAll'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Type::class,
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

        $this->productOptionConfig->expects($this->any())->method('getAll')->willReturn($allOptions);

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
