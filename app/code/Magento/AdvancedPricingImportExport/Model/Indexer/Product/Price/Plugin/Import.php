<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

class Import extends \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
{
    /**
     * After import handler
     */
    public function afterSaveAdvancedPricing()
    {
        $this->invalidateIndexer();
    }

    /**
     * After delete handler
     */
    public function afterDeleteAdvancedPricing()
    {
        $this->invalidateIndexer();
    }

    /**
     * After replace handler
     */
    public function afterReplaceAdvancedPricing()
    {
        $this->invalidateIndexer();
    }
}
