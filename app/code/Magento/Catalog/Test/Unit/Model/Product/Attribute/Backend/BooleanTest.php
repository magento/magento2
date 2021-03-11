<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Boolean as BooleanBackend;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;

class BooleanTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AbstractAttribute
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
