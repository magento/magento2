<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\Backup\Factory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->_model = new Factory($this->_objectManager);
    }

    public function testCreateWrongType()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->_model->create('WRONG_TYPE');
    }

    /**
     * @param string $type
     */
    #[DataProvider('allowedTypesDataProvider')]
    public function testCreate($type)
    {
        $this->_objectManager->expects($this->once())->method('create')->willReturn('ModelInstance');

        $this->assertEquals('ModelInstance', $this->_model->create($type));
    }

    /**
     * @return array
     */
    public static function allowedTypesDataProvider()
    {
        return [['db'], ['snapshot'], ['filesystem'], ['media'], ['nomedia']];
    }
}
