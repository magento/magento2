<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Model\Export;

use Magento\Downloadable\Model\LinkRepository;
use Magento\Downloadable\Model\SampleRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Downloadable\Model\Product\Type as Type;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable;

/**
 * Customizes output during export
 */
class RowCustomizer implements RowCustomizerInterface
{
    /**
     * @var array
     */
    private $downloadableData = [];

    /**
     * @var string[]
     */
    private $downloadableColumns = [
        Downloadable::COL_DOWNLOADABLE_LINKS,
        Downloadable::COL_DOWNLOADABLE_SAMPLES,
    ];

    /**
     * @var LinkRepository
     */
    private $linkRepository;

    /**
     * @var SampleRepository
     */
    private $sampleRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param LinkRepository $linkRepository
     * @param SampleRepository $sampleRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LinkRepository $linkRepository,
        SampleRepository $sampleRepository
    ) {
        $this->storeManager = $storeManager;
        $this->linkRepository = $linkRepository;
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * Prepare configurable data for export
     *
     * @param ProductCollection $collection
     * @param int[] $productIds
     * @return void
     */
    public function prepareData($collection, $productIds): void
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('type_id', ['eq' => Type::TYPE_DOWNLOADABLE])
            ->addAttributeToSelect('links_title')
            ->addAttributeToSelect('samples_title');
        // set global scope during export
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
        foreach ($collection as $product) {
            $productLinks = $this->linkRepository->getLinksByProduct($product);
            $productSamples = $this->sampleRepository->getSamplesByProduct($product);
            $this->downloadableData[$product->getId()] = [];
            $linksData = [];
            $samplesData = [];
            foreach ($productLinks as $linkId => $link) {
                $linkData = $link->getData();
                $linkData['group_title'] = $product->getData('links_title');
                $linksData[$linkId] = $this->optionRowToCellString($linkData);
            }
            foreach ($productSamples as $sampleId => $sample) {
                $sampleData = $sample->getData();
                $sampleData['group_title'] = $product->getData('samples_title');
                $samplesData[$sampleId] = $this->optionRowToCellString($sampleData);
            }
            $this->downloadableData[$product->getId()] = [
                Downloadable::COL_DOWNLOADABLE_LINKS => implode(
                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                    $linksData
                ),
                Downloadable::COL_DOWNLOADABLE_SAMPLES => implode(
                    Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                    $samplesData
                )];
        }
    }

    /**
     * Convert option row to cell string
     *
     * @param array $option
     * @return string
     */
    private function optionRowToCellString(array $option): string
    {
        $result = [];
        foreach ($option as $attributeCode => $value) {
            if ($value) {
                $result[] = $attributeCode . ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $value;
            }
        }
        return implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $result);
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function addHeaderColumns($columns): array
    {
        return array_merge($columns, $this->downloadableColumns);
    }

    /**
     * Add downloadable data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId): array
    {
        if (!empty($this->downloadableData[$productId])) {
            $dataRow = array_merge($dataRow, $this->downloadableData[$productId]);
        }
        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId): array
    {
        if (!empty($this->downloadableData[$productId])) {
            $additionalRowsCount = max($additionalRowsCount, count($this->downloadableData[$productId]));
        }
        return $additionalRowsCount;
    }
}
