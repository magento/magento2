<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Writer\Csv;
use Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo;
use Magento\Setup\Module\I18n\Factory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject(Factory::class);
    }

    /**
     * @param string $expectedInstance
     * @param string $fileName
     */
    #[DataProvider('createDictionaryWriterDataProvider')]
    public function testCreateDictionaryWriter($expectedInstance, $fileName)
    {
        $this->assertInstanceOf(
            $expectedInstance,
            $this->factory->createDictionaryWriter($fileName)
        );
    }

    /**
     * @return array
     */
    public static function createDictionaryWriterDataProvider()
    {
        return [
            [
                Csv::class,
                TESTS_TEMP_DIR . '/filename.invalid_type',
            ],
            [
                Csv::class,
                TESTS_TEMP_DIR . '/filename'
            ],
            [
                Csv::class,
                TESTS_TEMP_DIR . '/filename.csv'
            ],
            [
                Stdo::class,
                ''
            ],
        ];
    }
}
