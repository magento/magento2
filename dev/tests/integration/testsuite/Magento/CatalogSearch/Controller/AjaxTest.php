<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogSearch\Controller;

class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testSuggestAction()
    {
        $this->getRequest()->setParam('q', 'query_text');
        $this->dispatch('catalogsearch/ajax/suggest');
        $this->assertStringContainsString('query_text', $this->getResponse()->getBody());
    }
}
