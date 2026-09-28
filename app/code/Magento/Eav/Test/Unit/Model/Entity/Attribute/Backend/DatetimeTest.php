<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatetimeTest extends TestCase
{
    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezone;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attribute;

    /**
     * @var Datetime
     */
    private $model;

    protected function setUp(): void
    {
        $this->attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->model = new Datetime($this->timezone);
        $this->model->setAttribute($this->attribute);
    }

    /**
     * @throws LocalizedException
     */
    public function testGetDefaultValue()
    {
        $attributeName = 'attribute';
        $defaultValue = '2024-01-01 00:00:00';
        $this->attribute->expects($this->once())->method('getName')->willReturn($attributeName);
        $this->attribute->expects($this->exactly(2))->method('getDefaultValue')->willReturn($defaultValue);
        $object = new DataObject();
        $this->model->beforeSave($object);
        $this->assertEquals($defaultValue, $object->getData($attributeName));
    }
}
