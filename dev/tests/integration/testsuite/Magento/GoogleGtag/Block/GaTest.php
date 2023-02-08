<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\TestCase\AbstractController;

class GaTest extends AbstractController
{
    /**
     * Layout instance
     *
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatch('/');
        $this->layout = $this->_objectManager->get(LayoutInterface::class);
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
     * @magentoConfigFixture current_store google/gtag/analytics4/active 0
     * @magentoConfigFixture current_store google/gtag/google/analytics4/measurement_id G-XXXXXXXX
     */
    public function testBlockOutputIsEmptyWhenGaIsDisabled()
    {
        $this->assertEquals(
            false,
            $this->getGaBlockFromNode('head.additional')
        );
    }

    /**
     * Check, that block successfully gets rendered when configuration is
     * active.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store google/gtag/analytics4/active 1
     * @magentoConfigFixture current_store google/gtag/analytics4/measurement_id G-XXXXXXXX
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
        $childBlocks = $this->layout->getChildBlocks($nodeName);
        foreach ($childBlocks as $block) {
            if (strpos($block->getNameInLayout(), 'google_gtag_analytics') !== false) {
                return $block;
            }
        }
        return false;
    }
}
