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
     * Test if block exists in head tag
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store google/analytics/active 1
     * @magentoConfigFixture current_store google/analytics/account XXXXXXX
     */
    public function testBlockIsInHead()
    {
       $this->assertNotNull(
           $this->layout->getChildBlock('header.container', 'google_analytics')
       );
    }

    /**
     * Test if block does not exist in bpdy tag
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store google/analytics/active 1
     * @magentoConfigFixture current_store google/analytics/account XXXXXXX
     */
    public function testBlockIsNotInBody()
    {
        $this->assertFalse(
            $this->layout->getChildBlock('header.container', 'after.body.start')
        );
    }
}
