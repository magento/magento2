<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * @var Text
     */
    protected $elementText;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->elementText = $objectManagerHelper->getObject(Text::class);
    }

    public function testSetText()
    {
        $this->assertInstanceOf(Text::class, $this->elementText->setText('example'));
    }

    public function testGetText()
    {
        $this->elementText->setText('example');
        $this->assertEquals('example', $this->elementText->getText('example'));
    }

    /**
     * @param string $text
     * @param bool $before
     * @param string $expectedResult
     *
     * @dataProvider addTextDataProvider
     */
    public function testAddText($text, $before, $expectedResult)
    {
        $this->elementText->setText('example');
        $this->elementText->addText($text, $before);
        $this->assertEquals($expectedResult, $this->elementText->getText('example'));
    }

    /**
     * @return array
     */
    public static function addTextDataProvider()
    {
        return [
            'before_false' => [
                'text' => '_after',
                'before' => false,
                'expectedResult' => 'example_after',
            ],
            'before_true' => [
                'text' => 'before_',
                'before' => true,
                'expectedResult' => 'before_example',
            ],
        ];
    }
}
