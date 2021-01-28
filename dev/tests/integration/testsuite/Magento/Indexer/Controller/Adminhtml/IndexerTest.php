<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class IndexerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Assert that current page is index management page and that it has indexers mode selector
     *
     * @return void
     */
    public function testIndexersMode()
    {
        $this->dispatch('backend/indexer/indexer/list/');
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('<h1 class="page-title">Index Management</h1>', $body);
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="gridIndexer_massaction-select"]',
                $body
            ),
            'Mode selector is not found'
        );
        $this->assertStringContainsString('option value="change_mode_onthefly"', $body);
        $this->assertStringContainsString('option value="change_mode_changelog"', $body);
    }

    /**
     * Assert that index management contains a certain number of indexers
     *
     * @return void
     */
    public function testDefaultNumberOfIndexers()
    {
        $this->dispatch('backend/indexer/indexer/list/');
        $body = $this->getResponse()->getBody();

        $this->assertGreaterThanOrEqual(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@name="indexer_ids"]',
                $body
            ),
            'Indexer list is empty'
        );
    }
}
