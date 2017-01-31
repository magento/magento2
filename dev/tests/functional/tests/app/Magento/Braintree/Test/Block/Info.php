<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Payment information block.
 */
class Info extends Block
{
    /**
     * Braintree Payment information block locator.
     */
    private $info = './/tr';

    /**
     * Get Braintree payment information block data.
     *
     * @return array
     */
    public function getPaymentInfo()
    {
        $result = [];
        $elements = $this->_rootElement->getElements($this->info, Locator::SELECTOR_XPATH);
        foreach ($elements as $row) {
            $key = rtrim($row->find('./th', Locator::SELECTOR_XPATH)->getText(), ':');
            $value = $row->find('./td', Locator::SELECTOR_XPATH)->getText();
            $result[$key] = $value;
        }
        return $result;
    }
}
