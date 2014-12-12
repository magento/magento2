<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $this->assertContains('query_text', $this->getResponse()->getBody());
    }
}
