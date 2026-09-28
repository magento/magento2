<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Store\Model\StoreManagerInterface;

class WebsiteFilterOptions extends AbstractSource
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        return array_map(
            fn ($website) => ['value' => $website->getId(), 'label' => $website->getName()],
            $this->storeManager->getWebsites(false)
        );
    }
}
