<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test edit block
     */
    public function testEditBlock()
    {
        /**
         * @var \Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Edit
         */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\EncryptionKey\Block\Adminhtml\Crypt\Key\Edit::class
        );

        $this->assertEquals('Encryption Key', $block->getHeaderText());
    }
}
