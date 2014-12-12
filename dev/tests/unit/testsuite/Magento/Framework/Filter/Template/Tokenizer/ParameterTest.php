<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filter\Template\Tokenizer\Parameter
     */
    protected $_filter;

    protected function setUp()
    {
        $this->_filter = new Parameter();
    }

    /**
     * @param string $string String to tokenize
     * @param string $expectedValue
     * @dataProvider sampleTokenizeStringProvider
     */
    public function testTokenize($string, $expectedValue)
    {
        $this->_filter->setString($string);
        $this->assertEquals($expectedValue, $this->_filter->tokenize());
    }

    /**
     * @param string $string String to get value of
     * @param string $expectedValue
     * @dataProvider sampleGetValueStringProvider
     */
    public function testGetValue($string, $expectedValue)
    {
        $this->_filter->setString($string);
        $this->assertEquals($expectedValue, $this->_filter->getValue());
    }

    public function sampleTokenizeStringProvider()
    {
        return [
            [" direct_url='about-magento-demo-store'", ['direct_url' => 'about-magento-demo-store']],
            [" direct_url='about-magento-demo-store\\[newDemo]",
            ['direct_url' => 'about-magento-demo-store\\[newDemo]']],
            ["   ", []]
        ];
    }

    public function sampleGetValueStringProvider()
    {
        return [
            [" direct_url='about-magento-demo-store'", "direct_url='about-magento-demo-store'"],
            [" direct_url='about-magento-demo-store\\[newDemo]", "direct_url='about-magento-demo-store\\[newDemo]"],
            ['   ', '']
        ];
    }
}
