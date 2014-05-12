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
namespace Magento\Sales\Block\Order\Creditmemo;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Sales\Block\Order\Creditmemo\Items
     */
    protected $_block;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_block = $this->_layout->createBlock('Magento\Sales\Block\Order\Creditmemo\Items', 'block');
        $this->_creditmemo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Creditmemo'
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetTotalsHtml()
    {
        $childBlock = $this->_layout->addBlock('Magento\Framework\View\Element\Text', 'creditmemo_totals', 'block');

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getCreditmemo());
        $this->assertNotEquals($expectedHtml, $this->_block->getTotalsHtml($this->_creditmemo));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getTotalsHtml($this->_creditmemo);
        $this->assertSame($this->_creditmemo, $childBlock->getCreditmemo());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    public function testGetCommentsHtml()
    {
        $childBlock = $this->_layout->addBlock('Magento\Framework\View\Element\Text', 'creditmemo_comments', 'block');

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getEntity());
        $this->assertEmpty($childBlock->getTitle());
        $this->assertNotEquals($expectedHtml, $this->_block->getCommentsHtml($this->_creditmemo));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getCommentsHtml($this->_creditmemo);
        $this->assertSame($this->_creditmemo, $childBlock->getEntity());
        $this->assertNotEmpty($childBlock->getTitle());
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
