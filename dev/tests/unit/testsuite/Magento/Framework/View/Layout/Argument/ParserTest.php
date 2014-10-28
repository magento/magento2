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
namespace Magento\Framework\View\Layout\Argument;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '/_files/arguments.xml');
        $parser = new \Magento\Framework\View\Layout\Argument\Parser();
        $actual = $parser->parse($document->getElementsByTagName('argument')->item(0));
        $expected = array(
            'updater' => array('Updater1', 'Updater2'),
            'param' => array(
                'param1' => array('name' => 'param1', 'value' => 'Param Value 1'),
                'param2' => array('name' => 'param2', 'value' => 'Param Value 2')
            ),
            'item' => array(
                'item1' => array('name' => 'item1', 'value' => 'Item Value 1'),
                'item2' => array(
                    'name' => 'item2',
                    'item' => array('item3' => array('name' => 'item3', 'value' => 'Item Value 2.3'))
                )
            )
        );
        $this->assertSame($expected, $actual);
    }
}
