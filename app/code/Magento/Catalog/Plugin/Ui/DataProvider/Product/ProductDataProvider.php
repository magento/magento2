<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin\Ui\DataProvider\Product;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider as CatalogProductDataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Modify catalog product UI data with show total records flag.
 */
class ProductDataProvider
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Modify catalog product UI data with show total records flag.
     *
     * @param CatalogProductDataProvider $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(CatalogProductDataProvider $subject, array $data): array
    {
        return $this->addShowTotalRecords($data);
    }

    /**
     * Add flag to display/hide total records found and pagination elements in products grid header.
     *
     * @param array $data
     * @return array
     */
    private function addShowTotalRecords(array $data): array
    {
        if (key_exists('totalRecords', $data)) {
            if ($this->scopeConfig->getValue('admin/grid/limit_total_number_of_products')
                && $data['totalRecords'] >= $this->scopeConfig->getValue('admin/grid/records_limit')) {
                $data['showTotalRecords'] = false;
            } else {
                $data['showTotalRecords'] = true;
            }
        }

        return $data;
    }
}
