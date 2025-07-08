<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Boolean as BooleanBackend;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BooleanTest extends TestCase
{
    /**
     * @var MockObject|AbstractAttribute
     */
    private $attributeMock;

    /**
     * @var BooleanBackend
     */
    private $model;

    protected function setUp(): void
    {
        $this->attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            ['getName']
        );
        $this->model = new BooleanBackend();
        $this->model->setAttribute($this->attributeMock);
    }

    public function testBeforeSave()
    {
        $this->attributeMock->expects($this->any())->method('getName')->willReturn('attribute_name');
        $object = new DataObject([
            'use_config_attribute_name' => true,
        ]);
        $this->model->beforeSave($object);
        $this->assertEquals(BooleanSource::VALUE_USE_CONFIG, $object->getData('attribute_name'));
    }
}
