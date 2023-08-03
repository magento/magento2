<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Helper;

use Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable;

/**
 * Helper for import-export downloadable product
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @return bool
     */
    public function isRowDownloadableEmptyOptions(array $rowData): bool
    {
        return $this->isDataEmpty($rowData, Downloadable::COL_DOWNLOADABLE_LINKS)
            && $this->isDataEmpty($rowData, Downloadable::COL_DOWNLOADABLE_SAMPLES);
    }

    /**
     * Check whether the data is empty.
     *
     * @param array $data
     * @param string $key
     * @return bool
     */
    private function isDataEmpty(array $data, string $key): bool
    {
        return isset($data[$key]) && ($data[$key] == '' || $data[$key] == []);
    }

    /**
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @return bool
     */
    public function isRowDownloadableNoValid(array $rowData): bool
    {
        return isset($rowData[Downloadable::COL_DOWNLOADABLE_SAMPLES]) ||
            isset($rowData[Downloadable::COL_DOWNLOADABLE_LINKS]);
    }

    /**
     * Fill exist options
     *
     * @param array $base
     * @param array $option
     * @param array $existingOptions
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function fillExistOptions(array $base, array $option, array $existingOptions)
    {
        $result = [];
        foreach ($existingOptions as $existingOption) {
            if ($option['link_url'] == $existingOption['link_url']
                && $option['link_file'] == $existingOption['link_file']
                && $option['link_type'] == $existingOption['link_type']
                && $option['sample_url'] == $existingOption['sample_url']
                && $option['sample_file'] == $existingOption['sample_file']
                && $option['sample_type'] == $existingOption['sample_type']
                && $option['product_id'] == $existingOption['product_id']) {
                if (empty($existingOption['website_id'])) {
                    unset($existingOption['website_id']);
                }
                $result = array_replace($base, $option, $existingOption);
            }
        }
        return $result;
    }

    /**
     * Fill array data options for base entity
     *
     * @param array $base
     * @param array $replacement
     * @return array
     */
    public function prepareDataForSave(array $base, array $replacement)
    {
        $result = [];
        foreach ($replacement as $item) {
            $result[] = array_intersect_key($item, $base);
        }
        return $result;
    }

    /**
     * Get type parameters - file or url
     *
     * @param string $option
     * @return string
     */
    public function getTypeByValue($option)
    {
        $result = Downloadable::FILE_OPTION_VALUE;
        if (preg_match('/\bhttps?:\/\//i', $option)) {
            $result = Downloadable::URL_OPTION_VALUE;
        }
        return $result;
    }
}
