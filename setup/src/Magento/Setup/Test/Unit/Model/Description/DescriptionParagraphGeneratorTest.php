<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description;

class DescriptionParagraphGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\DescriptionSentenceGenerator
     */
    private $sentenceGeneratorMock;

    /**
     * @var \Magento\Setup\Model\Description\DescriptionParagraphGenerator
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

    public function setUp()
    {
        $this->sentenceGeneratorMock = $this->getMock(
            \Magento\Setup\Model\Description\DescriptionSentenceGenerator::class,
            [],
            [],
            '',
            false
        );
        $this->paragraphGenerator = new \Magento\Setup\Model\Description\DescriptionParagraphGenerator(
            $this->sentenceGeneratorMock,
            $this->paragraphConfig
        );
    }

    /**
     *
     */
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
