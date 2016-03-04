<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Export;

class AdvancedPricingStagingTest extends AdvancedPricingTest
{
    /**
     * @param array $skus
     */
    protected function modifyData($skus)
    {
        $this->objectManager->get('Magento\CatalogImportExport\Model\Version')->create($skus);
    }
}
