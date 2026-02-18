<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->converter = $objectManager->getObject(
            Converter::class
        );
    }

    /**
     * @param $internalType
     * @param $expected
     * @return void
     */
    #[DataProvider('convertProvider')]
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
    public static function convertProvider()
    {
        return [
            ['string', 'string'],
            ['float', 'double'],
            ['integer', 'integer'],
        ];
    }
}
