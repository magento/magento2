<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ConfigureTestDtos;
use Magento\Framework\Dto\Mock\ImmutableDtoWithEa;
use Magento\Framework\Dto\Mock\MutableDtoWithEa;
use Magento\Framework\Dto\Mock\MutableDtoWithIea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class DtoWithExtensionAttributesTest extends TestCase
{
    /**
     * @var DtoProcessor
     */
    private $dtoProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        ConfigureTestDtos::execute();

        $this->dtoProcessor = $objectManager->get(DtoProcessor::class);
    }

    public function testCreateImmutableDtoWithExtensionAttributes(): void
    {
        /** @var ImmutableDtoWithEa $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
                'extension_attributes' => [
                    'attribute1' => 'test1',
                    'attribute2' => 'test2',
                    'attribute3' =>[
                        'prop1' => 2,
                        'prop2' => 'abc',
                        'prop3' => ['jkl', 'mno'],
                        'prop4' => [5, 6, 7, 8]
                    ]
                ]
            ],
            ImmutableDtoWithEa::class
        );

        self::assertSame('test1', $dto->getExtensionAttributes()->getAttribute1());
        self::assertSame(['jkl', 'mno'], $dto->getExtensionAttributes()->getAttribute3()->getProp3());
    }

    public function testCreateMutableDtoWithExtensionAttributes(): void
    {
        /** @var MutableDtoWithEa $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
                'extension_attributes' => [
                    'attribute1' => 'test1',
                    'attribute2' => 'test2',
                    'attribute3' =>[
                        'prop1' => 2,
                        'prop2' => 'abc',
                        'prop3' => ['jkl', 'mno'],
                        'prop4' => [5, 6, 7, 8]
                    ]
                ]
            ],
            MutableDtoWithEa::class
        );

        self::assertSame('test1', $dto->getExtensionAttributes()->getAttribute1());
        self::assertSame(['jkl', 'mno'], $dto->getExtensionAttributes()->getAttribute3()->getProp3());
    }

    public function testCreateMutableDtoWithImmutableExtensionAttributes(): void
    {
        /** @var MutableDtoWithIea $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1,
                'prop2' => 'b',
                'prop3' => ['abc', 'def', 'ghi'],
                'prop4' => [1, 2, 3, 4],
                'extension_attributes' => [
                    'attribute1' => 'test1',
                    'attribute2' => 'test2',
                    'attribute3' =>[
                        'prop1' => 2,
                        'prop2' => 'abc',
                        'prop3' => ['jkl', 'mno'],
                        'prop4' => [5, 6, 7, 8]
                    ]
                ]
            ],
            MutableDtoWithIea::class
        );

        self::assertSame('test1', $dto->getExtensionAttributes()->getAttribute1());
        self::assertSame(['jkl', 'mno'], $dto->getExtensionAttributes()->getAttribute3()->getProp3());
    }
}
