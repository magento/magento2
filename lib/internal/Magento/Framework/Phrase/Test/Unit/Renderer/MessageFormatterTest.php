<?php

namespace Magento\Framework\Phrase\Test\Unit\Renderer;

use Magento\Framework\Phrase\Renderer\MessageFormatter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate;

class MessageFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    private function getMessageFormatter($locale)
    {
        $translateMock = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLocale'])
            ->getMock();
        $translateMock->method('getLocale')
            ->willReturn($locale);

        return $this->objectManager->getObject(MessageFormatter::class, ['translate' => $translateMock]);
    }

    /**
     * @param string $text The text with MessageFormat markers
     * @param string $locale
     * @param array $arguments The arguments supplying values for the variables
     * @param string $result The result of Phrase rendering
     *
     * @dataProvider renderMessageFormatterDataProvider
     */
    public function testRenderMessageFormatter($text, $locale, array $arguments, $result)
    {
        $renderer = $this->getMessageFormatter($locale);

        $this->assertEquals($result, $renderer->render([$text], $arguments));
    }

    /**
     * @return array
     */
    public function renderMessageFormatterDataProvider()
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
}
