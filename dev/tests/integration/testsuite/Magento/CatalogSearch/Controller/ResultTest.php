<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Controller;

class ResultTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/query.php
     * @magentoConfigFixture current_store general/locale/code de_DE
     */
    public function testIndexActionTranslation()
    {
        $this->markTestIncomplete('endTest() called too early, config fixture reseted');
        $this->getRequest()->setParam('q', 'query_text');
        $this->dispatch('catalogsearch/result');

        $responseBody = $this->getResponse()->getBody();
        $this->assertNotContains('for="search">Search', $responseBody);
        $this->assertStringMatchesFormat('%aSuche%S%a', $responseBody);

        $this->assertNotContains('Search entire store here...', $responseBody);
        $this->assertContains('Den gesamten Shop durchsuchen...', $responseBody);
    }
}
