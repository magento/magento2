<?php
/**
 * \Magento\Outbound\Formatter\JsonTest
 *
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
 * @copyright          Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license            http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Formatter;

require_once __DIR__ . '/JsonTest/Data.php';
class JsonTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Outbound\Formatter\Json */
    protected $_formatter;

    protected function setUp()
    {
        $this->_formatter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Outbound\Formatter\Json');
    }

    /**
     * @dataProvider encodeDataProvider
     *
     * @param $body
     * @param $formattedBody
     */
    public function testFormat($body, $formattedBody)
    {
        $this->assertSame($formattedBody, $this->_formatter->format($body));
    }

    /**
     * DataProvider for testing the JSON formatter.
     *
     * @return array
     */
    public function encodeDataProvider()
    {
        return array(
            array(array(), "[]"),
            array(
                array('a' => array('b' => 'c', 'd' => 'e'), 'f' => 'g'),
                '{"a":{"b":"c","d":"e"},"f":"g"}'
            ),
            array(array(null), '[null]'),
            array(array(true), '[true]'),
            array(array(false), '[false]'),
            array(array(-1), '[-1]'),
            array(array(0), '[0]'),
            array(array(1), '[1]'),
            array(array(2.4), '[2.4]'),
            array(array(2.0), '[2]'),
            array(
                array(new \Magento\Outbound\Formatter\JsonTest\Data('public', 'protected')),
                '[{"dataA":"public"}]'
            )
        );
    }
}
