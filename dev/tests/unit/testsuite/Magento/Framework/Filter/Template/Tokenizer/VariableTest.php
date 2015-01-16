<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filter\Template\Tokenizer\Variable
     */
    protected $_filter;

    protected function setUp()
    {
        $this->_filter = new Variable();
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

    public function sampleTokenizeStringProvider()
    {
        return [
            ["firstname", [['type' => 'variable', 'name' => 'firstname']]],
            [
                "invoke(arg1, arg2, 2, 2.7, -1, 'Mike\\'s')",
                [['type' => 'method', 'name' => 'invoke', 'args' => ['arg1', 'arg2', 2, 2.7, -1, "Mike's"]]]
            ],
            ["  ", []]
        ];
    }
}
