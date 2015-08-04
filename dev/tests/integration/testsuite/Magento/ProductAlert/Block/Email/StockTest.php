<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Block\Email;

class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ProductAlert\Block\Email\Stock
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\ProductAlert\Block\Email\Stock'
        );
    }
}
