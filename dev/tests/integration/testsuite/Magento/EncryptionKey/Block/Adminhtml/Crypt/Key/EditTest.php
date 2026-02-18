<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\EncryptionKey\Block\Adminhtml\Crypt\Key;

class EditTest extends \PHPUnit\Framework\TestCase
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
