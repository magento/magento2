<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\ValueTransformer;

use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\TextTransformer;
use PHPUnit\Framework\TestCase;

/**
 * Test value transformer
 */
class TextTransformerTest extends TestCase
{
    /**
     * @var TextTransformer
     */
    protected $model;

    /**
     * @var PreprocessorInterface
     */
    private $processorMock;

    /**
     * Setup method
     * @return void
     */
    public function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->processorMock = $this->createMock(PreprocessorInterface::class);
        $this->model = $objectManagerHelper->getObject(
            TextTransformer::class,
            [
                'preprocessors' => [
                    $this->processorMock
                ],
            ]
        );
    }

    /**
     * Test transform value
     *
     * @param string $value
     * @param string $expected
     * @return void
     * @dataProvider valuesDataProvider
     */
    public function testTransform(string $value, string $expected): void
    {
        $this->processorMock->expects($this->once())
            ->method('process')
            ->with($value)
            ->willReturnCallback('strtolower');
        $result = $this->model->transform($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * Values data provider
     *
     * @return array
     */
    public static function valuesDataProvider(): array
    {
        return [
            ['Laptop^camera{microphone}', 'laptop^camera{microphone}'],
            ['Birthday 25-Pack w/ Greatest of All Time Cupcake', 'birthday 25-pack w/ greatest of all time cupcake'],
            ['Retro vinyl record ~d123 *star', 'retro vinyl record ~d123 *star'],
        ];
    }
}
