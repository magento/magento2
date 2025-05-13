<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Ui\DataProvider\Product\Modifier\SpecialPriceAttributes;
use Magento\Directory\Model\Currency as DirectoryCurrency;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\NumberFormatter;
use Magento\Framework\NumberFormatterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecialPriceAttributesTest extends TestCase
{
    /**
     * @var ResolverInterface|MockObject
     */
    private $localResolver;

    /**
     * @var NumberFormatterFactory|MockObject
     */
    private $numberFormatterFactory;

    /**
     * @var SpecialPriceAttributes
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->localResolver = $this->createMock(ResolverInterface::class);
        $this->numberFormatterFactory = $this->createMock(NumberFormatterFactory::class);
        $this->model = new SpecialPriceAttributes(
            $this->createMock(DirectoryCurrency::class),
            $this->localResolver,
            ['attr1'],
            $this->numberFormatterFactory
        );
    }

    /**
     * @param string $locale
     * @param array $input
     * @param array $output
     * @return void
     * @dataProvider modifyDataProvider
     */
    public function testModifyData(string $locale, array $input, array $output): void
    {
        $this->localResolver->method('getLocale')
            ->willReturn($locale);
        $this->numberFormatterFactory->method('create')
            ->willReturnCallback(
                function (array $args) {
                    return new NumberFormatter(...array_values($args));
                }
            );
        $this->assertEquals($output, $this->model->modifyData($input));
    }

    /**
     * @return array
     */
    public static function modifyDataProvider(): array
    {
        return [
            [
                'en_US',
                [
                    'items' => [
                        [
                            'type_id' => 'simple',
                            'attr1' => '99',
                        ]
                    ]
                ],
                [
                    'items' => [
                        [
                            'type_id' => 'simple',
                            'attr1' => '99',
                        ]
                    ]
                ],
            ],
            [
                'en_US',
                [
                    'items' => [
                        [
                            'type_id' => 'simple',
                            'attr1' => '99',
                        ],
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '99',
                        ]
                    ]
                ],
                [
                    'items' => [
                        [
                            'type_id' => 'simple',
                            'attr1' => '99',
                        ],
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '99.000000%',
                        ]
                    ]
                ],
            ],
            [
                'en_US',
                [
                    'items' => [
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '9999',
                        ]
                    ]
                ],
                [
                    'items' => [
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '9,999.000000%',
                        ]
                    ]
                ],
            ],
            [
                'de_DE',
                [
                    'items' => [
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '9999',
                        ]
                    ]
                ],
                [
                    'items' => [
                        [
                            'type_id' => Type::TYPE_CODE,
                            'attr1' => '9.999,000000 %',
                        ]
                    ]
                ],
            ]
        ];
    }
}
