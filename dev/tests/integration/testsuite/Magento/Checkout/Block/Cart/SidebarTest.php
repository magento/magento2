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
namespace Magento\Checkout\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Sidebar
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Checkout\Block\Cart\Sidebar'
        );
        $this->_block->addChild('renderer.list', '\Magento\Framework\View\Element\RendererList');
        $this->_block->getChildBlock(
            'renderer.list'
        )->addChild(
            'default',
            '\Magento\Checkout\Block\Cart\Item\Renderer',
            array('template' => 'cart/item/default.phtml')
        );
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertEquals(
            array(
                'BLOCK_TPL',
                'default',
                $this->_block->getTemplateFile(),
                'template' => null,
                'item_renders' => 'default|Magento\Checkout\Block\Cart\Item\Renderer|cart/item/default.phtml'
            ),
            $this->_block->getCacheKeyInfo()
        );
    }
}
