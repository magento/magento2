<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Ui\DataProvider;

use Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider;
use Magento\Checkout\CustomerData\Cart;
use Magento\Framework\App\Config;
use Magento\Weee\Model\Config as WeeeConfig;

/**
 * Provide param on front, which says the current set of weee settings
 */
class WeeeSettings
{
    /**
     * WeeeSettings constructor.
     * @param Config $config
     */
    public function __construct(
        private Config $config
    ) {
    }

    /**
     * Add weee data to result
     *
     * @param Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        $result['displayWeee'] = $this->config
            ->getValue(WeeeConfig::XML_PATH_FPT_DISPLAY_PRODUCT_LIST);

        return $result;
    }
}
