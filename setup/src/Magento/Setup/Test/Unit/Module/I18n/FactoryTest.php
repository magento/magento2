<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Factory
     */
    protected $factory;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject(\Magento\Setup\Module\I18n\Factory::class);
    }

    /**
     * @param string $expectedInstance
     * @param string $fileName
     * @dataProvider createDictionaryWriterDataProvider
     */
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
    public function createDictionaryWriterDataProvider()
    {
        return [
            [
                \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
                TESTS_TEMP_DIR . '/filename.invalid_type',
            ],
            [
                \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
                TESTS_TEMP_DIR . '/filename'
            ],
            [
                \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
                TESTS_TEMP_DIR . '/filename.csv'
            ],
            [
                \Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo::class,
                ''
            ],
        ];
    }
}
