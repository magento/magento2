<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Text
     */
    protected $elementText;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->elementText = $objectManagerHelper->getObject(\Magento\Framework\View\Element\Text::class);
    }

    public function testSetText()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Element\Text::class, $this->elementText->setText('example'));
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
    public function addTextDataProvider()
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
