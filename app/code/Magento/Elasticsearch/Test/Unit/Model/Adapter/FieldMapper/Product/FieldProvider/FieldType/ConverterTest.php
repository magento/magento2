<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\FieldProvider\Product\FieldType;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Converter
     */
    private $converter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManagerHelper($this);

        $this->converter = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Converter::class,
            [
                'logger' => $this->logger,
            ]
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
