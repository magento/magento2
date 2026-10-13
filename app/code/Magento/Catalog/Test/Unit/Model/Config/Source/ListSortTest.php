<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Config\Source\ListSort;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListSortTest extends TestCase
{
    /**
     * @var ListSort
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $catalogConfig;

    protected function setUp(): void
    {
        $this->catalogConfig = $this->createMock(Config::class);

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            ListSort::class,
            ['catalogConfig' => $this->catalogConfig]
        );
    }

    public function testToOptionalArray()
    {
        $except = [
            ['label' => __('Position'), 'value' => 'position'],
            ['label' => 'testLabel', 'value' => 'testAttributeCode'],
        ];
        $this->catalogConfig->method('getAttributesUsedForSortBy')
            ->willReturn([['frontend_label' => 'testLabel', 'attribute_code' => 'testAttributeCode']]);

        $this->assertEquals($except, $this->model->toOptionArray());
    }
}
