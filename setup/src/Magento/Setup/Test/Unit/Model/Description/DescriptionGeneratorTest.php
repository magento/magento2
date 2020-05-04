<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description;

use Magento\Setup\Model\Description\DescriptionGenerator;
use Magento\Setup\Model\Description\DescriptionParagraphGenerator;
use Magento\Setup\Model\Description\MixinManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DescriptionGeneratorTest extends TestCase
{
    /**
     * @var MockObject|DescriptionParagraphGenerator
     */
    private $descriptionParagraphGeneratorMock;

    /**
     * @var MockObject|MixinManager
     */
    private $mixinManagerMock;

    /**
     * @var array
     */
    private $paragraphs = [
        'Paragraph#1', 'Paragraph#2', 'Paragraph#3'
    ];

    /**
     * @var array
     */
    private $descriptionConfigWithMixin = [
        'paragraphs' => [
            'count-min' => 3,
            'count-max' => 3
        ],
        'mixin' => [
            'tags' => ['p', 'b', 'div']
        ]
    ];

    /**
     * @var array
     */
    private $descriptionConfigWithoutMixin = [
        'paragraphs' => [
            'count-min' => 3,
            'count-max' => 3
        ]
    ];

    protected function setUp(): void
    {
        $this->descriptionParagraphGeneratorMock =
            $this->createMock(DescriptionParagraphGenerator::class);
        $this->descriptionParagraphGeneratorMock
            ->expects($this->exactly(3))
            ->method('generate')
            ->will($this->onConsecutiveCalls(
                $this->paragraphs[0],
                $this->paragraphs[1],
                $this->paragraphs[2]
            ));

        $this->mixinManagerMock = $this->createMock(MixinManager::class);
    }

    public function testGeneratorWithMixin()
    {
        $descriptionWithMixin = 'Some description with mixin';
        $this->mixinManagerMock
            ->expects($this->once())
            ->method('apply')
            ->with(
                implode(PHP_EOL, $this->paragraphs),
                $this->descriptionConfigWithMixin['mixin']['tags']
            )
            ->willReturn($descriptionWithMixin);

        $generator = new DescriptionGenerator(
            $this->descriptionParagraphGeneratorMock,
            $this->mixinManagerMock,
            $this->descriptionConfigWithMixin
        );

        $this->assertEquals($descriptionWithMixin, $generator->generate());
    }

    public function testGeneratorWithoutMixin()
    {
        $generator = new DescriptionGenerator(
            $this->descriptionParagraphGeneratorMock,
            $this->mixinManagerMock,
            $this->descriptionConfigWithoutMixin
        );

        $this->assertEquals(implode(PHP_EOL, $this->paragraphs), $generator->generate());
    }
}
