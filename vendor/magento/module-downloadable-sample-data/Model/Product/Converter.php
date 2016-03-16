<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableSampleData\Model\Product;

/**
 * Class Converter
 */
class Converter extends \Magento\CatalogSampleData\Model\Product\Converter
{
    /**
     * Get downloadable data from array
     *
     * @param array $row
     * @param array $downloadableData
     * @return array
     */
    public function getDownloadableData($row, $downloadableData = [])
    {
        $separatedData = $this->groupDownloadableData($row);
        $formattedData = $this->getFormattedData($separatedData);
        foreach (array_keys($formattedData) as $dataType) {
            $downloadableData[$dataType][] = $formattedData[$dataType];
        }

        return $downloadableData;
    }

    /**
     * Group downloadable data by link and sample array keys.
     *
     * @param array $downloadableData
     * @return array
     */
    public function groupDownloadableData($downloadableData)
    {
        $groupedData = [];
        foreach ($downloadableData as $dataKey => $dataValue) {
            if (!empty($dataValue)) {
                if ((preg_match('/^(link_item)/', $dataKey, $matches)) && is_array($matches)) {
                    $groupedData['link'][$dataKey] = $dataValue;
                }
            }
            unset($dataKey);
            unset($dataValue);
        }

        return $groupedData;
    }

    /**
     * Will format data corresponding to the product sample data array values.
     *
     * @param array $groupedData
     * @return array
     */
    public function getFormattedData($groupedData)
    {
        $formattedData = [];
        foreach (array_keys($groupedData) as $dataType) {
            if ($dataType == 'link') {
                $formattedData['link'] = $this->formatDownloadableLinkData($groupedData['link']);
            }
        }

        return $formattedData;
    }

    /**
     * Format downloadable link data
     *
     * @param array $linkData
     * @return array
     */
    public function formatDownloadableLinkData($linkData)
    {
        $linkItems = [
            'link_item_title',
            'link_item_price',
            'link_item_file',
        ];
        foreach ($linkItems as $csvRow) {
            $linkData[$csvRow] = isset($linkData[$csvRow]) ? $linkData[$csvRow] : '';
        }

        $link = [
            'is_delete' => '',
            'link_id' => '0',
            'title' => $linkData['link_item_title'],
            'price' => $linkData['link_item_price'],
            'number_of_downloads' => '0',
            'is_shareable' => '2',
            'type' => 'file',
            'file' => json_encode([['file' => $linkData['link_item_file'], 'status' => 'old']]),
            'sort_order' => '',
        ];

        return $link;
    }

    /**
     * Returns information about product's samples
     * @return array
     */
    public function getSamplesInfo()
    {
        $sample = [
            'is_delete' => '',
            'sample_id' => '0',
            'file' => json_encode([[
                'file' => '/l/u/luma_background_-_model_against_fence_4_sec_.mp4',
                'status' => 'old',
            ]]),
            'type' => 'file',
            'sort_order' => '',
        ];

        $samples = [];
        for ($i = 1; $i <= 3; $i++) {
            $sample['title'] = 'Trailer #' . $i;
            $samples[] = $sample;
        }

        return $samples;
    }
}
