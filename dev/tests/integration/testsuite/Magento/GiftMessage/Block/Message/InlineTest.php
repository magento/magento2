<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Message;

class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GiftMessage\Block\Message\Inline
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\GiftMessage\Block\Message\Inline'
        );
    }
}
