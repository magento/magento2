<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory;
use Magento\Catalog\Model\Layer\Filter\Decimal;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class DecimalTest extends TestCase
{
    public function testConstructorRequestVarIsOverwrittenCorrectlyInParent()
    {
        $attributeModel = $this->createPartialMock(
            Attribute::class,
            ['getAttributeCode']
        );
        $attributeModel->expects($this->once())->method('getAttributeCode')->willReturn('price1');

        $objectManager = new ObjectManager($this);

        $dataProviderFactory = $this->getMockBuilder(
            DecimalFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $instance = $objectManager->getObject(
            Decimal::class,
            [
                'data' => [
                    'attribute_model' => $attributeModel,
                ],
                'dataProviderFactory' => $dataProviderFactory
            ]
        );
        $this->assertSame('price1', $instance->getRequestVar());
    }
}
