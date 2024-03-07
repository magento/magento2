<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * The GraphQl test for product in non default store with different locale
 */
class ProductSearchWithTranslatedMessageTest extends GraphQlAbstract
{
    /**
     * Test translated error message in non default store
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Translation/_files/catalog_message_translate.php
     * @magentoConfigFixture fixture_second_store_store general/locale/code nl_NL
     */
    public function testErrorMessageTranslationInNonDefaultLocale()
    {
        CacheCleaner::clean(['translate', 'config']);
        $storeCode = "fixture_second_store";
        $header = ['Store' => $storeCode];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('currentPage-waarde moet groter zijn dan 0.');
        $this->graphQlQuery($this->getQuery(), [], '', $header);
    }

    private function getQuery()
    {
        return <<<QUERY
        {
            products( currentPage: 0) {
                items {
                    id
                    name
                }
            }
        }
        QUERY;
    }
}
