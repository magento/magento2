<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller;

class ResultTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     */
    public function testIndexActionTranslation()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Locale\ResolverInterface')->setLocale('de_DE');

        $this->getRequest()->setParam('q', 'query_text');
        $this->dispatch('catalogsearch/result');

        $responseBody = $this->getResponse()->getBody();
        $this->assertNotContains('for="search">Search', $responseBody);
        $this->assertStringMatchesFormat('%aSuche%S%a', $responseBody);

        $this->assertNotContains('Search entire store here...', $responseBody);
        $this->assertContains('Den gesamten Shop durchsuchen...', $responseBody);
    }

    public function testIndexActionXSSQueryVerification()
    {
        $this->getRequest()->setParam('q', '<script>alert(1)</script>');
        $this->dispatch('catalogsearch/result');

        $responseBody = $this->getResponse()->getBody();
        $data = '<script>alert(1)</script>';
        $this->assertNotContains($data, $responseBody);
        $this->assertContains(htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false), $responseBody);
    }
}
