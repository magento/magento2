<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $attributeFactoryMock;

    /**
     * @var BooleanSource
     */
    private $model;

    protected function setUp(): void
    {
        $this->attributeFactoryMock = $this->createMock(
            AttributeFactory::class
        );
        $this->model = new BooleanSource($this->attributeFactoryMock);
    }

    public function testGetAllOptions()
    {
        $expectedResult = [
            ['label' => __('Yes'), 'value' => BooleanSource::VALUE_YES],
            ['label' => __('No'), 'value' => BooleanSource::VALUE_NO],
            ['label' => __('Use config'), 'value' => BooleanSource::VALUE_USE_CONFIG],
        ];
        $this->assertEquals($expectedResult, $this->model->getAllOptions());
    }
}
