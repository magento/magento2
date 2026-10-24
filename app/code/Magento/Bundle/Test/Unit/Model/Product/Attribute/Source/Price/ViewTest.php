<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product\Attribute\Source\Price;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Bundle\Model\Product\Attribute\Source\Price\View;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $model;

    /**
     * @var Option|MockObject
     */
    protected $option;

    /**
     * @var OptionFactory|MockObject
     */
    protected $optionFactory;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attribute;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->option = $this->createMock(Option::class);
        $this->optionFactory = $this->createPartialMock(
            OptionFactory::class,
            ['create']
        );
        $this->optionFactory->method('create')->willReturn($this->option);
        $this->attribute = $this->createMock(AbstractAttribute::class);

        $this->model = (new ObjectManager($this))
            ->getObject(
                View::class,
                [
                    'optionFactory' => $this->optionFactory,
                ]
            );
        $this->model->setAttribute($this->attribute);
    }

    /**
     * @return void
     */
    public function testGetAllOptions()
    {
        $options = $this->model->getAllOptions();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('value', $option);
        }
    }

    /**
     * @return void
     */
    public function testGetOptionTextForExistLabel()
    {
        $existValue = 1;

        $this->assertInstanceOf(Phrase::class, $this->model->getOptionText($existValue));
    }

    /**
     * @return void
     */
    public function testGetOptionTextForNotExistLabel()
    {
        $notExistValue = -1;

        $this->assertFalse($this->model->getOptionText($notExistValue));
    }

    /**
     * @return void
     */
    public function testGetFlatColumns()
    {
        $code = 'attribute-code';
        $this->attribute->method('getAttributeCode')->willReturn($code);

        $columns = $this->model->getFlatColumns();

        $this->assertIsArray($columns);
        $this->assertArrayHasKey($code, $columns);

        foreach ($columns as $column) {
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertArrayHasKey('extra', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('nullable', $column);
            $this->assertArrayHasKey('comment', $column);
        }
    }

    /**
     * @return void
     */
    public function testGetFlatUpdateSelect()
    {
        $store = 1;
        $select = 'select';

        $this->option->expects($this->once())
            ->method('getFlatUpdateSelect')
            ->with($this->attribute, $store, false)
            ->willReturn($select);

        $this->assertEquals($select, $this->model->getFlatUpdateSelect($store));
    }
}
