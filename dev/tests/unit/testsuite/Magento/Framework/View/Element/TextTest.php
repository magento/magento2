<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Element;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Text
     */
    protected $elementText;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->elementText = $objectManagerHelper->getObject('Magento\Framework\View\Element\Text');
    }

    public function testSetText()
    {
        $this->assertInstanceOf('Magento\Framework\View\Element\Text', $this->elementText->setText('example'));
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
