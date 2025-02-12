<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\ResourceModel\Product\Website as ProductWebsiteResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for OptionSelectBuilderInterface to filter by website assignments.
 */
class ScopedOptionSelectBuilder
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductWebsiteResource
     */
    private $productWebsiteResource;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductWebsiteResource $productWebsiteResource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductWebsiteResource $productWebsiteResource
    ) {
        $this->storeManager = $storeManager;
        $this->productWebsiteResource = $productWebsiteResource;
    }

    /**
     * Add website filter to select.
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(OptionSelectBuilderInterface $subject, Select $select)
    {
        $store = $this->storeManager->getStore();
        $select->joinInner(
            ['entity_website' => $this->productWebsiteResource->getMainTable()],
            'entity_website.product_id = entity.entity_id AND entity_website.website_id = ' . $store->getWebsiteId(),
            []
        );

        return $select;
    }
}
