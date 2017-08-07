<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule;

/**
 * Class \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider
 *
 * @since 2.1.0
 */
class WebsitesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     * @since 2.1.0
     */
    private $store;

    /**
     * @param \Magento\Store\Model\System\Store $store
     * @since 2.1.0
     */
    public function __construct(\Magento\Store\Model\System\Store $store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return $this->store->getWebsiteValuesForForm();
    }
}
