<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Rule;

/**
 * Class \Magento\CatalogRule\Model\Rule\WebsitesOptionsProvider
 *
 */
class WebsitesOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $store;

    /**
     * @param \Magento\Store\Model\System\Store $store
     */
    public function __construct(\Magento\Store\Model\System\Store $store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->store->getWebsiteValuesForForm();
    }
}
