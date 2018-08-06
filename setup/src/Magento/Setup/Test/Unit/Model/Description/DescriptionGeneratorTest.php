<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description;

class DescriptionGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\DescriptionParagraphGenerator
     */
    private $descriptionParagraphGeneratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\MixinManager
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

    public function setUp()
    {
        $this->descriptionParagraphGeneratorMock =
            $this->createMock(\Magento\Setup\Model\Description\DescriptionParagraphGenerator::class);
        $this->descriptionParagraphGeneratorMock
            ->expects($this->exactly(3))
            ->method('generate')
            ->will($this->onConsecutiveCalls(
                $this->paragraphs[0],
                $this->paragraphs[1],
                $this->paragraphs[2]
            ));

        $this->mixinManagerMock = $this->createMock(\Magento\Setup\Model\Description\MixinManager::class);
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

        $generator = new \Magento\Setup\Model\Description\DescriptionGenerator(
            $this->descriptionParagraphGeneratorMock,
            $this->mixinManagerMock,
            $this->descriptionConfigWithMixin
        );

        $this->assertEquals($descriptionWithMixin, $generator->generate());
    }

    public function testGeneratorWithoutMixin()
    {
        $generator = new \Magento\Setup\Model\Description\DescriptionGenerator(
            $this->descriptionParagraphGeneratorMock,
            $this->mixinManagerMock,
            $this->descriptionConfigWithoutMixin
        );

        $this->assertEquals(implode(PHP_EOL, $this->paragraphs), $generator->generate());
    }
}
