<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->assertContains('<h1 class="page-title">Index Management</h1>', $body);
        $this->assertSelectCount('#gridIndexer_massaction-select', 1, $body, 'Mode selector is not found');
        $this->assertContains('option value="change_mode_onthefly"', $body);
        $this->assertContains('option value="change_mode_changelog"', $body);
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
        $this->assertSelectCount(
            '[name="indexer_ids"]',
            true,
            $body,
            'Indexer list is empty'
        );
    }
}
