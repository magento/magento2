<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Factory
     */
    protected $factory;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject('Magento\Setup\Module\I18n\Factory');
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
                'Magento\Setup\Module\I18n\Dictionary\Writer\Csv',
                TESTS_TEMP_DIR . '/filename.invalid_type',
            ],
            [
                'Magento\Setup\Module\I18n\Dictionary\Writer\Csv',
                TESTS_TEMP_DIR . '/filename'
            ],
            [
                'Magento\Setup\Module\I18n\Dictionary\Writer\Csv',
                TESTS_TEMP_DIR . '/filename.csv'
            ],
            [
                'Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo',
                ''
            ],
        ];
    }
}
