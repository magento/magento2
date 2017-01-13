<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Boolean as BooleanBackend;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;

class BooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractAttribute
     */
    private $attributeMock;

    /**
     * @var BooleanBackend
     */
    private $model;

    protected function setUp()
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
