<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Api\Data\OptionTypeInterface;
use Magento\Bundle\Api\Data\OptionTypeInterfaceFactory;
use Magento\Bundle\Model\OptionTypeList;
use Magento\Bundle\Model\Source\Option\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionTypeListTest extends TestCase
{
    /**
     * @var OptionTypeList
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $typeMock;

    /**
     * @var MockObject
     */
    protected $typeFactoryMock;

    protected function setUp(): void
    {
        $this->typeMock = $this->createMock(Type::class);
        $this->typeFactoryMock = $this->createPartialMock(
            OptionTypeInterfaceFactory::class,
            ['create']
        );
        $this->model = new OptionTypeList(
            $this->typeMock,
            $this->typeFactoryMock
        );
    }

    public function testGetItems()
    {
        $this->typeMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn([['value' => 'value', 'label' => 'label']]);

        $typeMock = $this->getMockForAbstractClass(OptionTypeInterface::class);
        $typeMock->expects($this->once())->method('setCode')->with('value')->willReturnSelf();
        $typeMock->expects($this->once())->method('setLabel')->with('label')->willReturnSelf();
        $this->typeFactoryMock->expects($this->once())->method('create')->willReturn($typeMock);
        $this->assertEquals([$typeMock], $this->model->getItems());
    }
}
