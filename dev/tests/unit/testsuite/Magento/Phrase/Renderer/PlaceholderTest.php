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
namespace Magento\Phrase\Renderer;

class PlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Placeholder */
    protected $_renderer;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_renderer = $objectManager->getObject('Magento\Phrase\Renderer\Placeholder');
    }

    /**
     * @param string $text The text with placeholders
     * @param array $arguments The arguments supplying values for the placeholders
     * @param string $result The result of Phrase rendering
     *
     * @dataProvider renderPlaceholderDataProvider
     */
    public function testRenderPlaceholder($text, array $arguments, $result)
    {
        $this->assertEquals($result, $this->_renderer->render($text, $arguments));
    }

    /**
     * @return array
     */
    public function renderPlaceholderDataProvider()
    {
        return [
            [
                'text %1 %2',
                ['one', 'two'],
                'text one two'
            ],
            [
                'text %one %two',
                ['one' => 'one', 'two' => 'two'],
                'text one two'
            ],
            [
                '%one text %two %1',
                ['one' => 'one', 'two' => 'two', 'three'],
                'one text two three'
            ],
            [
                'text %1 %two %2 %3 %five %4 %5',
                ['one', 'two' => 'two', 'three', 'four', 'five' => 'five', 'six', 'seven'],
                'text one two three four five six seven'
            ],
            [
                '%one text %two text %three %1 %2',
                ['two' => 'two', 'one' => 'one', 'three' => 'three', 'four', 'five'],
                'one text two text three four five'
            ],
            [
                '%three text %two text %1',
                ['two' => 'two', 'three' => 'three', 'one'],
                'three text two text one'
            ],
            [
                'text %1 text %2 text',
                [],
                'text %1 text %2 text'
            ],
            [
                '%1 text %2',
                ['one'],
                'one text %2'
            ]
        ];
    }
}
