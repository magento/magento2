<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Factory
     */
    protected $factory;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject('Magento\Tools\I18n\Factory');
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
                'Magento\Tools\I18n\Dictionary\Writer\Csv',
                'filename.invalid_type',
            ],
            [
                'Magento\Tools\I18n\Dictionary\Writer\Csv',
                'filename'
            ],
            [
                'Magento\Tools\I18n\Dictionary\Writer\Csv',
                'filename.csv'
            ],
            [
                'Magento\Tools\I18n\Dictionary\Writer\Csv\Stdo',
                ''
            ],
        ];
    }
}
