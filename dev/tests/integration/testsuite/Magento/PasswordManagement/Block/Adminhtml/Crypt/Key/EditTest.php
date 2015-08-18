<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Block\Adminhtml\Crypt\Key;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test edit block
     */
    public function testEditBlock()
    {
        /**
         * @var \Magento\PasswordManagement\Block\Adminhtml\Crypt\Key\Edit
         */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\PasswordManagement\Block\Adminhtml\Crypt\Key\Edit'
        );

        $this->assertEquals('Encryption Key', $block->getHeaderText());
    }
}
