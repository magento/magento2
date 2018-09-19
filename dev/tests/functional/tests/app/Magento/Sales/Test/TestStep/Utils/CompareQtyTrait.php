<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep\Utils;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Compare qty in current order with qty in the entity form.
 */
trait CompareQtyTrait
{
    /**
     * Compare items.
     *
     * @param FixtureInterface[] $products
     * @param array $data
     * @return bool
     */
    protected function compare(array $products, array $data)
    {
        if (empty($data['items_data'])) {
            return false;
        }

        $count = 0;
        foreach ($data['items_data'] as $key => $item) {
            if (!isset($products[$key])) {
                continue;
            }

            if ($products[$key]->getData('qty') !== $item['qty']) {
                ++$count;
            }
        }

        return $count !== 0;
    }
}
