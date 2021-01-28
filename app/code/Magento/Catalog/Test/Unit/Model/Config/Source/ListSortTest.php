<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ListSortTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\ListSort
     */
    private $model;

    /**
     * @var \Magento\Catalog\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $catalogConfig;

    protected function setUp(): void
    {
        $this->catalogConfig = $this->getMockBuilder(\Magento\Catalog\Model\Config::class)
            ->disableOriginalConstructor()->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Config\Source\ListSort::class,
            ['catalogConfig' => $this->catalogConfig]
        );
    }

    public function testToOptionalArray()
    {
        $except = [
            ['label' => __('Position'), 'value' => 'position'],
            ['label' => 'testLabel', 'value' => 'testAttributeCode'],
        ];
        $this->catalogConfig->expects($this->any())->method('getAttributesUsedForSortBy')
            ->willReturn([['frontend_label' => 'testLabel', 'attribute_code' => 'testAttributeCode']]);

        $this->assertEquals($except, $this->model->toOptionArray());
    }
}
