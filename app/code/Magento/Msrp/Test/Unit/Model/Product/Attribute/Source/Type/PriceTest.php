<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Model\Product\Attribute\Source\Type;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Msrp\Model\Product\Attribute\Source\Type\Price;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(Price::class);
    }

    public function testGetFlatColumns()
    {
        $abstractAttributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getAttributeCode', '__wakeup']
        );

        $abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('code');

        $this->_model->setAttribute($abstractAttributeMock);

        $flatColumns = $this->_model->getFlatColumns();

        $this->assertIsArray($flatColumns, 'FlatColumns must be an array value');
        $this->assertNotEmpty($flatColumns, 'FlatColumns must be not empty');
        foreach ($flatColumns as $result) {
            $this->assertArrayHasKey('unsigned', $result, 'FlatColumns must have "unsigned" column');
            $this->assertArrayHasKey('default', $result, 'FlatColumns must have "default" column');
            $this->assertArrayHasKey('extra', $result, 'FlatColumns must have "extra" column');
            $this->assertArrayHasKey('type', $result, 'FlatColumns must have "type" column');
            $this->assertArrayHasKey('nullable', $result, 'FlatColumns must have "nullable" column');
        }
    }
}
