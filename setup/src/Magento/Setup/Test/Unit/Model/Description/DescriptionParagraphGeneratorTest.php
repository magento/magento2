<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description;

use Magento\Setup\Model\Description\DescriptionParagraphGenerator;
use Magento\Setup\Model\Description\DescriptionSentenceGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DescriptionParagraphGeneratorTest extends TestCase
{
    /**
     * @var MockObject|DescriptionSentenceGenerator
     */
    private $sentenceGeneratorMock;

    /**
     * @var DescriptionParagraphGenerator
     */
    private $paragraphGenerator;

    /**
     * @var array
     */
    private $paragraphConfig = [
        'sentences' => [
            'count-min' => 4,
            'count-max' => 4
        ]
    ];

    protected function setUp(): void
    {
        $this->sentenceGeneratorMock =
            $this->createMock(DescriptionSentenceGenerator::class);
        $this->paragraphGenerator = new DescriptionParagraphGenerator(
            $this->sentenceGeneratorMock,
            $this->paragraphConfig
        );
    }

    public function testParagraphGeneration()
    {
        // @codingStandardsIgnoreStart
        $consecutiveSentences = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
            'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
        ];
        // @codingStandardsIgnoreEnd

        $this->sentenceGeneratorMock
            ->expects($this->exactly(4))
            ->method('generate')
            ->will($this->onConsecutiveCalls(
                $consecutiveSentences[0],
                $consecutiveSentences[1],
                $consecutiveSentences[2],
                $consecutiveSentences[3]
            ));

        $this->assertEquals(
            implode(' ', $consecutiveSentences),
            $this->paragraphGenerator->generate()
        );
    }
}
