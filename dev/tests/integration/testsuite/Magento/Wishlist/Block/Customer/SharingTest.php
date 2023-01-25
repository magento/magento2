<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Block\Customer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class test share wish list block.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class SharingTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Sharing */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Sharing::class);
    }

    /**
     * @return void
     */
    public function testDisplayWishListSharingForm(): void
    {
        $elementsXpath = [
            'Emails input' => "//form[contains(@class, 'share')]//textarea[@name='emails' and @id='email_address']",
            'Message input' => "//form[contains(@class, 'share')]//textarea[@name='message' and @id='message']",
            'Share button' => "//form[contains(@class, 'share')]//button[contains(@class, 'submit')]"
                . "/span[contains(text(), 'Share Wish List')]",
        ];
        $blockHtml = $this->block->setTemplate('Magento_Wishlist::sharing.phtml')->toHtml();
        foreach ($elementsXpath as $element => $xpath) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath($xpath, $blockHtml),
                sprintf("%s was not found.", $element)
            );
        }
    }
}
