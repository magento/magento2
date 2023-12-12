<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting max_items_in_order_summary from storeConfig query
 */
class GetMaxItemsInOrderSummaryTest extends GraphQlAbstract
{
    private const MAX_ITEMS_TO_DISPLAY = 5;

    #[
        Config('checkout/options/max_items_display_count', self::MAX_ITEMS_TO_DISPLAY)
    ]
    public function testGetMaxItemsInOrderSummary()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);
        $responseDataObject = new DataObject($response);

        self::assertEquals(self::MAX_ITEMS_TO_DISPLAY, $responseDataObject->getData('storeConfig/max_items_in_order_summary'));
    }

    /**
     * Create storeConfig query
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
{
  storeConfig {
    max_items_in_order_summary
  }
}
QUERY;
    }
}
