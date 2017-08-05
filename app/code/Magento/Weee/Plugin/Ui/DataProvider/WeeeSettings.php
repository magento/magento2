<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Plugin\Ui\DataProvider;

use Magento\Framework\App\Config;

/**
 * Provide param on front, which says the current set of weee settings
 */
class WeeeSettings
{
    /**
     * @var Config
     */
    private $config;

    /**
     * WeeeSettings constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add weee data to result
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(\Magento\Catalog\Ui\DataProvider\Product\Listing\DataProvider $subject, $result)
    {
        $result['displayWeee'] = $this->config
            ->getValue(\Magento\Weee\Model\Config::XML_PATH_FPT_DISPLAY_PRODUCT_LIST);

        return $result;
    }
}
