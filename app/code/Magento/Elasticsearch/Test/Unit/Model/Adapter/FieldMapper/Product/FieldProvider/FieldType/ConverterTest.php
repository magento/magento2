<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD)
 */
class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Converter
     */
    private $converter;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->converter = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Converter::class
        );
    }

    /**
     * @dataProvider convertProvider
     * @param $internalType
     * @param $expected
     * @return void
     */
    public function testConvert($internalType, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->converter->convert($internalType)
        );
    }

    /**
     * @return array
     */
    public function convertProvider()
    {
        return [
            ['string', 'string'],
            ['float', 'float'],
            ['integer', 'integer'],
        ];
    }
}
