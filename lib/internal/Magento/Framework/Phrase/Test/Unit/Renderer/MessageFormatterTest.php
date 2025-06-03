<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\MessageFormatter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate;
use PHPUnit\Framework\TestCase;

/**
 * Tests that messages sent through the MessageFormatter phrase renderer result in what would be expected when sent
 * through PHP's native MessageFormatter, and that the locale is pulled from the Translate dependency
 */
class MessageFormatterTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Retrieve test cases
     *
     * @return array [Raw Phrase, Locale, Arguments, Expected Result]
     * @throws \Exception
     */
    public static function renderMessageFormatterDataProvider(): array
    {
        $twentynineteenJuneTwentyseven = new \DateTime('2019-06-27');

        return [
            [
                'A table has {legs, plural, =0 {no legs} =1 {one leg} other {# legs}}.',
                'en_US',
                ['legs' => 4],
                'A table has 4 legs.'
            ],
            [
                'A table has {legs, plural, =0 {no legs} =1 {one leg} other {# legs}}.',
                'en_US',
                ['legs' => 0],
                'A table has no legs.'
            ],
            [
                'A table has {legs, plural, =0 {no legs} =1 {one leg} other {# legs}}.',
                'en_US',
                ['legs' => 1],
                'A table has one leg.'
            ],
            ['The table costs {price, number, currency}.', 'en_US', ['price' => 23.4], 'The table costs $23.40.'],
            [
                'Today is {date, date, long}.',
                'en_US',
                ['date' => $twentynineteenJuneTwentyseven],
                'Today is June 27, 2019.'
            ],
            [
                'Today is {date, date, long}.',
                'ja_JP',
                ['date' => $twentynineteenJuneTwentyseven],
                'Today is 2019年6月27日.'
            ],
        ];
    }

    /**
     * Test MessageFormatter
     *
     * @param string $text The text with MessageFormat markers
     * @param string $locale
     * @param array $arguments The arguments supplying values for the variables
     * @param string $result The expected result of Phrase rendering
     *
     * @dataProvider renderMessageFormatterDataProvider
     */
    public function testRenderMessageFormatter(string $text, string $locale, array $arguments, string $result): void
    {
        $renderer = $this->getMessageFormatter($locale);

        $this->assertEquals($result, $renderer->render([$text], $arguments));
    }

    /**
     * Create a MessageFormatter object provided a locale
     *
     * Automatically sets up the Translate dependency to return the provided locale and returns a MessageFormatter
     * that has been provided that dependency
     *
     * @param string $locale
     * @return MessageFormatter
     */
    private function getMessageFormatter(string $locale): MessageFormatter
    {
        $translateMock = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLocale'])
            ->getMock();
        $translateMock->method('getLocale')
            ->willReturn($locale);

        return $this->objectManager->getObject(MessageFormatter::class, ['translate' => $translateMock]);
    }
}
