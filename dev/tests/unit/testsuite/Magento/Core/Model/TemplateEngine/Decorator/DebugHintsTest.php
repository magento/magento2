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
namespace Magento\Core\Model\TemplateEngine\Decorator;

class DebugHintsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $showBlockHints
     * @dataProvider renderDataProvider
     */
    public function testRender($showBlockHints)
    {
        $subject = $this->getMock('Magento\Framework\View\TemplateEngineInterface');
        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface', array(), array(), 'TestBlock', false);
        $subject->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            $this->identicalTo($block),
            'template.phtml',
            array('var' => 'val')
        )->will(
            $this->returnValue('<div id="fixture"/>')
        );
        $model = new DebugHints($subject, $showBlockHints);
        $actualResult = $model->render($block, 'template.phtml', array('var' => 'val'));
        $this->assertSelectEquals('div > div[title="template.phtml"]', 'template.phtml', 1, $actualResult);
        $this->assertSelectCount('div > div#fixture', 1, $actualResult);
        $this->assertSelectEquals('div > div[title="TestBlock"]', 'TestBlock', (int)$showBlockHints, $actualResult);
    }

    public function renderDataProvider()
    {
        return array('block hints disabled' => array(false), 'block hints enabled' => array(true));
    }
}
