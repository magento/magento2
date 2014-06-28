<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            [" direct_url='about-magento-demo-store\\[newDemo]", ['direct_url' => 'about-magento-demo-store[newDemo]']],
            ["   ", []]
        ];
    }

    public function sampleGetValueStringProvider()
    {
        return [
            [" direct_url='about-magento-demo-store'", "direct_url='about-magento-demo-store'"],
            [" direct_url='about-magento-demo-store\\[newDemo]", "direct_url='about-magento-demo-store[newDemo]"],
            ['   ', '']
        ];
    }
}
