<?php
/**
 * Import entity of downloadable product type
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model\Import\Product\Type;

use Magento\Framework\EntityManager\MetadataPool;
use \Magento\Store\Model\Store;

/**
 * Class Downloadable
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Downloadable extends \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
{
    /**
     * Pair value separator.
     */
    const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Default sort order
     */
    const DEFAULT_SORT_ORDER = 0;

    /**
     * Default number of downloads
     */
    const DEFAULT_NUMBER_OF_DOWNLOADS = 0;

    /**
     * Default is shareable
     */
    const DEFAULT_IS_SHAREABLE = 2;

    /**
     * Default website id
     */
    const DEFAULT_WEBSITE_ID = 0;

    /**
     * Patch for downloadable files samples
     */
    const DOWNLOADABLE_PATCH_SAMPLES = 'downloadable/files/samples';

    /**
     * Patch for downloadable files links
     */
    const DOWNLOADABLE_PATCH_LINKS = 'downloadable/files/links';

    /**
     * Patch for downloadable files link samples
     */
    const DOWNLOADABLE_PATCH_LINK_SAMPLES = 'downloadable/files/link_samples';

    /**
     * Type option for url
     */
    const URL_OPTION_VALUE = 'url';

    /**
     * Type option for file
     */
    const FILE_OPTION_VALUE = 'file';

    /**
     * Column with downloadable samples
     */
    const COL_DOWNLOADABLE_SAMPLES = 'downloadable_samples';

    /**
     * Column with downloadable links
     */
    const COL_DOWNLOADABLE_LINKS = 'downloadable_links';

    /**
     * Default group title
     */
    const DEFAULT_GROUP_TITLE = '';

    /**
     * Default links can be purchased separately
     */
    const DEFAULT_PURCHASED_SEPARATELY = 1;

    /**
     * Error codes.
     */
    const ERROR_OPTIONS_NOT_FOUND = 'optionsNotFound';

    const ERROR_GROUP_TITLE_NOT_FOUND = 'groupTitleNotFound';

    const ERROR_OPTION_NO_TITLE = 'optionNoTitle';

    const ERROR_MOVE_FILE = 'moveFile';

    const ERROR_COLS_IS_EMPTY = 'emptyOptions';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_OPTIONS_NOT_FOUND => 'Options for downloadable products not found',
        self::ERROR_GROUP_TITLE_NOT_FOUND => 'Group titles not found for downloadable products',
        self::ERROR_OPTION_NO_TITLE => 'Option no title',
        self::ERROR_MOVE_FILE => 'Error move file',
        self::ERROR_COLS_IS_EMPTY => 'Missing sample and links data for the downloadable product'
    ];

    /**
     * Entity model parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Ids products
     *
     * @var array
     */
    protected $productIds = [];

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $cachedOptions = [
        'link' => [],
        'sample' => []
    ];

    /**
     * Instance of empty sample
     *
     * @var array
     */
    protected $dataSample = [
        'sample_id' => null,
        'product_id' => null,
        'sample_url' => null,
        'sample_file' => null,
        'sample_type' => null,
        'sort_order' => self::DEFAULT_SORT_ORDER
    ];

    /**
     * Instance of empty sample title
     *
     * @var array
     */
    protected $dataSampleTitle = [
        'sample_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => null
    ];

    /**
     * Instance of empty link
     *
     * @var array
     */
    protected $dataLink = [
        'link_id' => null,
        'product_id' => null,
        'sort_order' => self::DEFAULT_SORT_ORDER,
        'number_of_downloads' => self::DEFAULT_NUMBER_OF_DOWNLOADS,
        'is_shareable' => self::DEFAULT_IS_SHAREABLE,
        'link_url' => null,
        'link_file' => null,
        'link_type' => null,
        'sample_url' => null,
        'sample_file' => null,
        'sample_type' => null
    ];

    /**
     * Instance of empty link title
     *
     * @var array
     */
    protected $dataLinkTitle = [
        'link_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => null
    ];

    /**
     * Instance of empty link price
     *
     * @var array
     */
    protected $dataLinkPrice = [
        'price_id' => null,
        'link_id' => null,
        'website_id' => self::DEFAULT_WEBSITE_ID,
        'price' => null
    ];

    /**
     * Option link mapping.
     *
     * @var array
     */
    protected $optionLinkMapping = [
        'sortorder' => 'sort_order',
        'downloads' => 'number_of_downloads',
        'shareable' => 'is_shareable',
        'url' => 'link_url',
        'file' => 'link_file',
    ];

    /**
     * Option sample mapping.
     *
     * @var array
     */
    protected $optionSampleMapping = [
        'sortorder' => 'sort_order',
        'url' => 'sample_url',
        'file' => 'sample_file',
    ];

    /**
     * Num row parsing file
     */
    protected $rowNum;

    /**
     * @var \Magento\DownloadableImportExport\Helper\Uploader
     */
    protected $uploaderHelper;

    /**
     * @var \Magento\DownloadableImportExport\Helper\Data
     */
    protected $downloadableHelper;

    /**
     * Downloadable constructor
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param \Magento\DownloadableImportExport\Helper\Uploader $uploaderHelper
     * @param \Magento\DownloadableImportExport\Helper\Data $downloadableHelper
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\DownloadableImportExport\Helper\Uploader $uploaderHelper,
        \Magento\DownloadableImportExport\Helper\Data $downloadableHelper,
        MetadataPool $metadataPool
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $metadataPool);
        $this->parameters = $this->_entityModel->getParameters();
        $this->_resource = $resource;
        $this->connection = $resource->getConnection('write');
        $this->uploaderHelper = $uploaderHelper;
        $this->downloadableHelper = $downloadableHelper;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        while ($bunch = $this->_entityModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum)) {
                    continue;
                }
                $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];
                if ($this->_type != $productData['type_id']) {
                    continue;
                }
                $this->parseOptions($rowData, $productData[$this->getProductEntityLinkField()]);
            }
            if (!empty($this->cachedOptions['sample']) || !empty($this->cachedOptions['link'])) {
                $this->saveOptions();
                $this->clear();
            }
        }
        return $this;
    }

    /**
     * Validate row attributes. Pass VALID row data ONLY as argument.
     *
     * @param array $rowData
     * @param int $rowNum
     * @param bool $isNewProduct Optional
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error = false;
        if (!$this->downloadableHelper->isRowDownloadableNoValid($rowData)) {
            $this->_entityModel->addRowError(self::ERROR_OPTIONS_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        if ($this->downloadableHelper->isRowDownloadableEmptyOptions($rowData)) {
            $this->_entityModel->addRowError(self::ERROR_COLS_IS_EMPTY, $this->rowNum);
            $error = true;
        }
        if ($this->isRowValidSample($rowData) || $this->isRowValidLink($rowData)) {
            $error = true;
        }
        return !$error;
    }

    /**
     * Validation sample options
     *
     * @param array $rowData
     * @return bool
     */
    protected function isRowValidSample(array $rowData)
    {
        $result = false;
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])
            && $rowData[self::COL_DOWNLOADABLE_SAMPLES] != ''
            && $this->sampleGroupTitle($rowData) == '') {
            $this->_entityModel->addRowError(self::ERROR_GROUP_TITLE_NOT_FOUND, $this->rowNum);
            $result = true;
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])
            && $rowData[self::COL_DOWNLOADABLE_SAMPLES] != '') {
            $result = $this->isTitle($this->prepareSampleData($rowData[self::COL_DOWNLOADABLE_SAMPLES]));
        }
        return $result;
    }

    /**
     * Validation links option
     *
     * @param array $rowData
     * @return bool
     */
    protected function isRowValidLink(array $rowData)
    {
        $result = false;
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS]) &&
            $rowData[self::COL_DOWNLOADABLE_LINKS] != '' &&
            $this->linksAdditionalAttributes($rowData, 'group_title', self::DEFAULT_GROUP_TITLE) == ''
        ) {
            $this->_entityModel->addRowError(self::ERROR_GROUP_TITLE_NOT_FOUND, $this->rowNum);
            $result = true;
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS]) &&
            $rowData[self::COL_DOWNLOADABLE_LINKS] != ''
        ) {
            $result = $this->isTitle($this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS]));
        }
        return $result;
    }

    /**
     * Check isset title for all options
     *
     * @param array $options
     * @return bool
     */
    protected function isTitle(array $options)
    {
        $result = false;
        foreach ($options as $option) {
            if (!array_key_exists('title', $option)) {
                $this->_entityModel->addRowError(self::ERROR_OPTION_NO_TITLE, $this->rowNum);
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Prepare attributes with default value for save.
     *
     * @param array $rowData
     * @param bool $withDefaultValue
     * @return array
     */
    public function prepareAttributesWithDefaultValueForSave(array $rowData, $withDefaultValue = true)
    {
        $resultAttrs = parent::prepareAttributesWithDefaultValueForSave($rowData, $withDefaultValue);
        $resultAttrs = array_merge($resultAttrs, $this->addAdditionalAttributes($rowData));
        return $resultAttrs;
    }

    /**
     * Get additional attributes for dowloadable product.
     *
     * @param array $rowData
     * @return array
     */
    protected function addAdditionalAttributes(array $rowData)
    {
        return [
            'samples_title' => $this->sampleGroupTitle($rowData),
            'links_title' => $this->linksAdditionalAttributes($rowData, 'group_title', self::DEFAULT_GROUP_TITLE),
            'links_purchased_separately' => $this->linksAdditionalAttributes(
                $rowData,
                'purchased_separately',
                self::DEFAULT_PURCHASED_SEPARATELY
            )
        ];
    }

    /**
     * Get additional attributes for links
     *
     * @param array $rowData
     * @param string $attribute
     * @param mixed $defaultValue
     * @return string
     */
    protected function linksAdditionalAttributes(array $rowData, $attribute, $defaultValue)
    {
        $result = $defaultValue;
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData[self::COL_DOWNLOADABLE_LINKS]
            );
            foreach ($options as $option) {
                $arr = $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr[$attribute])) {
                    $result = $arr[$attribute];
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Get group title for sample
     *
     * @param array $rowData
     * @return string
     */
    protected function sampleGroupTitle(array $rowData)
    {
        $result = '';
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData[self::COL_DOWNLOADABLE_SAMPLES]
            );
            foreach ($options as $option) {
                $arr = $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr['group_title'])) {
                    $result = $arr['group_title'];
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Parse options for products
     *
     * @param array $rowData
     * @param int $entityId
     * @return $this
     */
    protected function parseOptions(array $rowData, $entityId)
    {
        $this->productIds[] = $entityId;
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS])) {
            $this->cachedOptions['link'] = array_merge(
                $this->cachedOptions['link'],
                $this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS], $entityId)
            );
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])) {
            $this->cachedOptions['sample'] = array_merge(
                $this->prepareSampleData($rowData[self::COL_DOWNLOADABLE_SAMPLES], $entityId),
                $this->cachedOptions['sample']
            );
        }
        return $this;
    }

    /**
     * Get fill data options with key sample
     *
     * @param array $base
     * @param array $options
     * @return array
     */
    protected function fillDataSample(array $base, array $options)
    {
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_sample')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach ($options as $option) {
            $isExist = false;
            foreach ($existingOptions as $existingOption) {
                if ($option['sample_url'] == $existingOption['sample_url']
                    && $option['sample_file'] == $existingOption['sample_file']
                    && $option['sample_type'] == $existingOption['sample_type']
                    && $option['product_id'] == $existingOption['product_id']) {
                    $result[] = array_replace($this->dataSampleTitle, $option, $existingOption);
                    $isExist = true;
                }
            }
            if (!$isExist) {
                $result[] = array_replace($base, $option);
            }
        }
        return $result;
    }

    /**
     * Get fill data options with key link
     *
     * @param array $base
     * @param array $options
     * @return array
     */
    protected function fillDataLink(array $base, array $options)
    {
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_link')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach ($options as $option) {
            $existOption = $this->downloadableHelper->fillExistOptions($base, $option, $existingOptions);
            if (empty($existOption)) {
                $result[] = array_replace($base, $option);
            } else {
                $result[] = $existOption;
            }
        }
        return $result;
    }

    /**
     * Get fill data options with key link
     *
     * @param array $options
     * @return array
     */
    protected function fillDataTitleLink(array $options)
    {
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                ['dl' => $this->_resource->getTableName('downloadable_link')],
                [
                    'link_id',
                    'product_id',
                    'sort_order',
                    'number_of_downloads',
                    'is_shareable',
                    'link_url',
                    'link_file',
                    'link_type',
                    'sample_url',
                    'sample_file',
                    'sample_type'
                ]
            )->joinLeft(
                ['dlp' => $this->_resource->getTableName('downloadable_link_price')],
                'dl.link_id = dlp.link_id AND dlp.website_id=' . self::DEFAULT_WEBSITE_ID,
                ['price_id', 'website_id']
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach ($options as $option) {
            $existOption = $this->downloadableHelper->fillExistOptions($this->dataLinkTitle, $option, $existingOptions);
            if (!empty($existOption)) {
                $result['title'][] = $existOption;
            }
            $existOption = $this->downloadableHelper->fillExistOptions($this->dataLinkPrice, $option, $existingOptions);
            if (!empty($existOption)) {
                $result['price'][] = $existOption;
            }
        }
        return $result;
    }

    /**
     * Upload all sample files
     *
     * @param array $options
     * @return array
     */
    protected function uploadSampleFiles(array $options)
    {
        $result = [];
        foreach ($options as $option) {
            if ($option['sample_file'] !== null) {
                $option['sample_file'] = $this->uploadDownloadableFiles($option['sample_file'], 'samples', true);
            }
            $result[] = $option;
        }
        return $result;
    }

    /**
     * Upload all link files
     *
     * @param array $options
     * @return array
     */
    protected function uploadLinkFiles(array $options)
    {
        $result = [];
        foreach ($options as $option) {
            if ($option['sample_file'] !== null) {
                $option['sample_file'] = $this->uploadDownloadableFiles($option['sample_file'], 'link_samples', true);
            }
            if ($option['link_file'] !== null) {
                $option['link_file'] = $this->uploadDownloadableFiles($option['link_file'], 'links', true);
            }
            $result[] = $option;
        }
        return $result;
    }

    /**
     * Save options in base
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function saveOptions()
    {
        $options = $this->cachedOptions;
        if (!empty($options['sample'])) {
            $this->saveSampleOptions();
        }
        if (!empty($options['link'])) {
            $this->saveLinkOptions();
        }
        return $this;
    }

    /**
     * Save sample options
     *
     * @return $this
     */
    protected function saveSampleOptions()
    {
        $options['sample'] = $this->uploadSampleFiles($this->cachedOptions['sample']);
        $dataSample = $this->fillDataSample($this->dataSample, $options['sample']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample'),
            $this->downloadableHelper->prepareDataForSave($this->dataSample, $dataSample)
        );
        $titleSample = $this->fillDataSample($this->dataSampleTitle, $options['sample']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample_title'),
            $this->downloadableHelper->prepareDataForSave($this->dataSampleTitle, $titleSample)
        );
        return $this;
    }

    /**
     * Save link options
     *
     * @return $this
     */
    protected function saveLinkOptions()
    {
        $options['link'] = $this->uploadLinkFiles($this->cachedOptions['link']);
        $dataLink = $this->fillDataLink($this->dataLink, $options['link']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link'),
            $this->downloadableHelper->prepareDataForSave($this->dataLink, $dataLink)
        );
        $dataLink = $this->fillDataTitleLink($options['link']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link_title'),
            $this->downloadableHelper->prepareDataForSave($this->dataLinkTitle, $dataLink['title'])
        );
        if (count($dataLink['price'])) {
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_price'),
                $this->downloadableHelper->prepareDataForSave($this->dataLinkPrice, $dataLink['price'])
            );
        }
        return $this;
    }

    /**
     * Prepare string to array data sample
     *
     * @param string $rowCol
     * @param int $entityId
     * @return array
     */
    protected function prepareSampleData($rowCol, $entityId = null)
    {
        $result = [];
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach ($options as $option) {
            $result[] = array_merge(
                $this->dataSample,
                ['product_id' => $entityId],
                $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option))
            );
        }
        return $result;
    }

    /**
     * Prepare string to array data link
     *
     * @param string $rowCol
     * @param string $entityId
     * @return array
     */
    protected function prepareLinkData($rowCol, $entityId = null)
    {
        $result = [];
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach ($options as $option) {
            $result[] = array_merge(
                $this->dataLink,
                ['product_id' => $entityId],
                $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option))
            );
        }
        return $result;
    }

    /**
     * Parse the link option.
     *
     * @param array $values
     * @return array
     */
    protected function parseLinkOption(array $values)
    {
        $option = [];
        foreach ($values as $keyValue) {
            $keyValue = trim($keyValue);
            if ($pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR)) {
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);
                if ($key == 'sample') {
                    $option['sample_type'] = $this->downloadableHelper->getTypeByValue($value);
                    $option['sample_' . $option['sample_type']] = $value;
                }
                if ($key == self::URL_OPTION_VALUE || $key == self::FILE_OPTION_VALUE) {
                    $option['link_type'] = $key;
                }
                if ($key == 'downloads' && $value == 'unlimited') {
                    $value = 0;
                }
                if (isset($this->optionLinkMapping[$key])) {
                    $key = $this->optionLinkMapping[$key];
                }
                $option[$key] = $value;
            }
        }
        return $option;
    }

    /**
     * Parse the sample option.
     *
     * @param array $values
     * @return array
     */
    protected function parseSampleOption($values)
    {
        $option = [];
        foreach ($values as $keyValue) {
            $keyValue = trim($keyValue);
            if ($pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR)) {
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);
                if ($key == self::URL_OPTION_VALUE || $key == self::FILE_OPTION_VALUE) {
                    $option['sample_type'] = $key;
                }
                if (isset($this->optionSampleMapping[$key])) {
                    $key = $this->optionSampleMapping[$key];
                }
                $option[$key] = $value;
            }
        }
        return $option;
    }

    /**
     * Uploading files into the "downloadable/files" media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @param string $type
     * @param bool $renameFileOff
     * @return string
     */
    protected function uploadDownloadableFiles($fileName, $type = 'links', $renameFileOff = false)
    {
        try {
            $res = $this->uploaderHelper->getUploader($type, $this->parameters)->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
            $this->_entityModel->addRowError(self::ERROR_MOVE_FILE, $this->rowNum);
            return '';
        }
    }

    /**
     * Clear cached values between bunches
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function clear()
    {
        $this->cachedOptions = [
            'link' => [],
            'sample' => []
        ];
        $this->productIds = [];
        return $this;
    }
}
