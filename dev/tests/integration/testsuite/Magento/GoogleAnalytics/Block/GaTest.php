<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleAnalytics\Block;

use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController;

class GaTest extends AbstractController
{
    /**
     * Layout instance
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dispatch('/');
        $this->layout = ObjectManager::getInstance()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
    }

    /**
     * Check for correct position of GA block
     */
    public function testBlockPresentInHead()
    {
        $this->assertNotNull(
            $this->getGaBlockFromNode('head.additional')
        );
    }

    /**
     * Test that block has been successfully moved
     * from body to head tag.
     */
    public function testBlockIsAbsentInBody()
    {
        $this->assertFalse(
            $this->getGaBlockFromNode('after.body.start')
        );
    }

    /**
     * Test null output when GA is disabled
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store google/analytics/active 0
     * @magentoConfigFixture current_store google/analytics/account XXXXXXX
     */
    public function testBlockOutputIsEmptyWhenGaIsDisabled()
    {
        $this->assertEquals(
            "",
            $this->getGaBlockFromNode('head.additional')->toHtml()
        );
    }

    /**
     * Check, that block successfully gets rendered when configuration is
     * active.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store google/analytics/active 1
     * @magentoConfigFixture current_store google/analytics/account XXXXXXX
     */
    public function testBlockOutputExistsWhenGaIsEnabled()
    {
        $this->assertNotEquals(
            "",
            $this->getGaBlockFromNode('head.additional')->toHtml()
        );
    }

    /**
     * Get GA block
     *
     * @param string $nodeName
     * @return \Magento\Framework\View\Element\AbstractBlock|false
     */
    private function getGaBlockFromNode($nodeName = 'head.additional')
    {
        return $this->layout->getChildBlock($nodeName, 'google_analytics');
    }
}
