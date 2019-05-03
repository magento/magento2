<?php
/**
 * RowCustomizer
 *
 * @copyright Copyright © 2019 Firebear Studio. All rights reserved.
 * @author    Firebear Studio <fbeardev@gmail.com>
 */
declare(strict_types=1);

namespace Magento\DownloadableImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Downloadable\Model\Link as DownloadableLink;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Downloadable\Model\Sample as DownloadableSample;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use function implode;

class RowCustomizer implements RowCustomizerInterface
{
    /**
     * Header column for Configurable Product variations
     */
    const DOWNLOADABLE_LINKS_COLUMN = 'downloadable_links';

    /**
     * Header column for Configurable Product variation labels
     */
    const DOWNLOADABLE_SAMPLE_COLUMN = 'downloadable_samples';
    /**
     * @var array
     */
    protected $downloadableData = [];
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var string[]
     */
    private $downloadableColumns = [
        self::DOWNLOADABLE_LINKS_COLUMN,
        self::DOWNLOADABLE_SAMPLE_COLUMN,
    ];

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int[] $productIds
     *
     * @return mixed
     */
    public function prepareData($collection, $productIds)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('type_id', ['eq' => DownloadableProductType::TYPE_DOWNLOADABLE]);

        // set global scope during export
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        while ($product = $productCollection->fetchItem()) {
            /** @var DownloadableProductType $downloadableTypeInstance */
            $downloadableTypeInstance = $product->getTypeInstance();
            $downloadableLinksData = $downloadableTypeInstance->getLinks($product);
            $sampleLinksData = $downloadableTypeInstance->getSamples($product);
            $this->downloadableData[$product->getId()] = [];
            $downloadData = [];
            $sampleData = [];
            $links = [];

            /** @var DownloadableLink $downloadableLinkDatum */
            foreach ($downloadableLinksData as $downloadableLinkDatum) {
                $links[] = [
                    DownloadableLink::KEY_TITLE . '=' . $downloadableLinkDatum->getTitle(),
                    DownloadableLink::KEY_SORT_ORDER . '=' . $downloadableLinkDatum->getSortOrder(),
                    DownloadableLink::KEY_IS_SHAREABLE . '=' . $downloadableLinkDatum->getIsShareable(),
                    DownloadableLink::KEY_PRICE . '=' . $downloadableLinkDatum->getPrice(),
                    DownloadableLink::KEY_NUMBER_OF_DOWNLOADS . '=' . $downloadableLinkDatum->getNumberOfDownloads(),
                    DownloadableLink::KEY_LINK_TYPE . '=' . $downloadableLinkDatum->getLinkType(),
                    DownloadableLink::KEY_LINK_FILE . '=' . $downloadableLinkDatum->getLinkFile(),
                    DownloadableLink::KEY_LINK_URL . '=' . $downloadableLinkDatum->getLinkUrl(),
                    DownloadableLink::KEY_SAMPLE_TYPE . '=' . $downloadableLinkDatum->getSampleType(),
                    DownloadableLink::KEY_SAMPLE_FILE . '=' . $downloadableLinkDatum->getSampleFile(),
                    DownloadableLink::KEY_SAMPLE_URL . '=' . $downloadableLinkDatum->getSampleUrl(),
                    'group_title=Links'
                ];
            }
            foreach ($links as $link) {
                $downloadData[] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $link);
            }

            $links = [];
            /** @var DownloadableSample $sampleLinksDatum */
            foreach ($sampleLinksData as $sampleLinksDatum) {
                $links[] = [
                    DownloadableSample::KEY_TITLE . '=' . $sampleLinksDatum->getTitle(),
                    DownloadableSample::KEY_SORT_ORDER . '=' . $sampleLinksDatum->getSortOrder(),
                    DownloadableSample::KEY_SAMPLE_TYPE . '=' . $sampleLinksDatum->getSampleType(),
                    DownloadableSample::KEY_SAMPLE_FILE . '=' . $sampleLinksDatum->getSampleFile(),
                    DownloadableSample::KEY_SAMPLE_URL . '=' . $sampleLinksDatum->getSampleUrl(),
                    'group_title=Samples'
                ];
            }
            foreach ($links as $link) {
                $sampleData[] = implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $link);
            }
            $this->downloadableData[$product->getId()] = [
                self::DOWNLOADABLE_LINKS_COLUMN => implode(
                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                    $downloadData
                ),
                self::DOWNLOADABLE_SAMPLE_COLUMN => implode(
                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                    $sampleData
                ),
            ];
        }
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function addHeaderColumns($columns)
    {
        return array_merge($columns, $this->downloadableColumns);
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     *
     * @return mixed
     */
    public function addData($dataRow, $productId)
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
     *
     * @return mixed
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        if (!empty($this->downloadableData[$productId])) {
            $additionalRowsCount = max($additionalRowsCount, count($this->downloadableData[$productId]));
        }
        return $additionalRowsCount;
    }
}
