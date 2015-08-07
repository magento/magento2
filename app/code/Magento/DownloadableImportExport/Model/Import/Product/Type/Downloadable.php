<?php
/**
 * Import entity of downloadable product type
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableImportExport\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Store\Model\Store;

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
    ];

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $fileUploader;

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
        'link_id' => null,
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
     * List file for delete
     *
     * @var array
     */
    protected $fileForDelete = [];

    /**
     * Num row parsing file
     */
    protected $rowNum;

    /**
     * File helper downloadable prduct
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\Resource $resource
     * @param array $params
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\Resource $resource,
        array $params,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Downloadable\Helper\File $fileHelper
    ){
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->parameters = $this->_entityModel->getParameters();
        $this->_resource = $resource;
        $this->connection = $resource->getConnection('write');
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->fileHelper = $fileHelper;
    }

    /**
     * Save product type specific data.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveData()
    {
        if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            $newSku = $this->_entityModel->getNewSku();
            while ($bunch = $this->_entityModel->getNextBunch()) {
                foreach ($bunch as $rowNum => $rowData) {
                    $productData = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]];
                    $this->productIds[] = $productData['entity_id'];
                }
                $this->deleteFiles();
            }
        } else {
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
                    $this->parseOptions($rowData, $productData['entity_id']);
                }
                if (!empty($this->cachedOptions['sample']) || !empty($this->cachedOptions['link'])) {
                    if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE) {
                        $this->deleteOptions();
                    }
                    $this->saveOptions();
                    $this->clear();
                }
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
     */
    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error = false;
        if (!$this->isRowDownloadableNoValid($rowData)){
            $this->_entityModel->addRowError(self::ERROR_OPTIONS_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS]) &&
            $rowData[self::COL_DOWNLOADABLE_LINKS] != '' &&
            $this->linksAdditionalAttributes($rowData, 'group_title', self::DEFAULT_GROUP_TITLE) == '')
        {
            $this->_entityModel->addRowError(self::ERROR_GROUP_TITLE_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES]) &&
            $rowData[self::COL_DOWNLOADABLE_SAMPLES] != '' &&
            $this->sampleGroupTitle($rowData) == '')
        {
            $this->_entityModel->addRowError(self::ERROR_GROUP_TITLE_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS]) &&
            $rowData[self::COL_DOWNLOADABLE_LINKS] != '')
        {
            $error = $this->isTitle($this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS]));
        }
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES]) &&
            $rowData[self::COL_DOWNLOADABLE_SAMPLES] != '')
        {
            $error = $this->isTitle($this->prepareSampleData($rowData[self::COL_DOWNLOADABLE_SAMPLES]));
        }
        return !$error;
    }

    /**
     * Check isset title for all options
     *
     * @param array $options
     * @return bool
     */
    protected function isTitle(array $options){
        $result = false;
        foreach($options as $option){
            if (!array_key_exists('title', $option)){
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
     * Check whether the row is valid.
     *
     * @param array $rowData
     * @return bool
     */
    protected function isRowDownloadableNoValid(array $rowData)
    {
        $result = isset($rowData[self::COL_DOWNLOADABLE_SAMPLES]) ||
            isset($rowData[self::COL_DOWNLOADABLE_LINKS]);
        return $result;
    }

    /**
     * Save list file for delete entity
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteFiles(){
        $this->deleteSampleFiles();
        $this->deleteLinkFiles();
        return $this;
    }

    /**
     * Delete sample files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteSampleFiles(){
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_sample')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach($existingOptions as $option){
            if ($option['sample_file'] !== null){
                unlink(self::DOWNLOADABLE_PATCH_SAMPLES . $option['sample_file']);
            }
        }
        return $this;
    }

    /**
     * Delete link files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteLinkFiles(){
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_link')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach($existingOptions as $option){
            if ($option['sample_file'] !== null){
                unlink(self::DOWNLOADABLE_PATCH_LINK_SAMPLES . $option['sample_file']);
            }
            if ($option['link_file'] !== null){
                unlink(self::DOWNLOADABLE_PATCH_LINKS . $option['link_file']);
            }
        }
        return $this;
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
            'links_purchased_separately' => $this->linksAdditionalAttributes($rowData, 'purchased_separately', self::DEFAULT_PURCHASED_SEPARATELY)
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
    protected function linksAdditionalAttributes(array $rowData, $attribute, $defaultValue){
        $result = $defaultValue;
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData[self::COL_DOWNLOADABLE_LINKS]
            );
            foreach ($options as $option) {
                $arr = $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr[$attribute])){
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
    protected function sampleGroupTitle(array $rowData){
        $result = '';
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData[self::COL_DOWNLOADABLE_SAMPLES]
            );
            foreach ($options as $option) {
                $arr = $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr['group_title'])){
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
     * @param array $values
     * @param int $entityId
     * @return
     */
    protected function parseOptions(array $rowData, $entityId){
        $this->productIds[] = $entityId;
        if (isset($rowData[self::COL_DOWNLOADABLE_LINKS]))
            $this->cachedOptions['link'] = array_merge(
                $this->cachedOptions['link'],
                $this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS], $entityId)
            );
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES]))
            $this->cachedOptions['sample'] = array_merge(
                $this->prepareSampleData($rowData[self::COL_DOWNLOADABLE_SAMPLES], $entityId),
                $this->cachedOptions['sample']
            );
        return $this;
    }

    /**
     * Get fill data options with key sample
     *
     * @param array $options
     * @return array
     */
    protected function fillDataTitleSample(array $options){
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_sample')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach($options as $option){
            foreach($existingOptions as $existingOption){
                if( $option['sample_url'] == $existingOption['sample_url'] &&
                    $option['sample_file'] == $existingOption['sample_file'] &&
                    $option['sample_type'] == $existingOption['sample_type'] &&
                    $option['product_id'] == $existingOption['product_id']
                ){
                    $result[] = array_replace($this->dataSampleTitle, $option, $existingOption);
                }
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
    protected function fillDataLink(array $options){
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->_resource->getTableName('downloadable_link')
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach($options as $option){
            foreach($existingOptions as $existingOption){
                if( $option['link_url'] == $existingOption['link_url'] &&
                    $option['link_file'] == $existingOption['link_file'] &&
                    $option['link_type'] == $existingOption['link_type'] &&
                    $option['sample_url'] == $existingOption['sample_url'] &&
                    $option['sample_file'] == $existingOption['sample_file'] &&
                    $option['sample_type'] == $existingOption['sample_type'] &&
                    $option['product_id'] == $existingOption['product_id']
                ){
                    $result['title'][] = array_replace($this->dataLinkTitle, $option, $existingOption);
                    $result['price'][] = array_replace($this->dataLinkPrice, $option, $existingOption);
                }
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
    protected function prepareDataForSave(array $base, array $replacement){
        $result = [];
        foreach($replacement as $item){
            $result[] = array_intersect_key($item, $base);
        }
        return $result;
    }

    /**
     * Upload all sample files
     *
     * @param array $options
     * @return array
     */
    protected function uploadSampleFiles(array $options){
        $result = [];
        foreach($options as $option){
            if ($option['sample_file'] !== null){
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
    protected function uploadLinkFiles(array $options){
        $result = [];
        foreach($options as $option){
            if ($option['sample_file'] !== null){
                $option['sample_file'] = $this->uploadDownloadableFiles($option['sample_file'], 'link_samples', true);
            }
            if ($option['link_file'] !== null){
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
    protected function saveOptions(){
        $options = $this->cachedOptions;
        if (!empty($options['sample'])) {
            $options['sample'] = $this->uploadSampleFiles($options['sample']);
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_sample'),
                $this->prepareDataForSave($this->dataSample, $options['sample'])
            );
            $titleSample = $this->fillDataTitleSample($options['sample']);
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_sample_title'),
                $this->prepareDataForSave($this->dataSampleTitle, $titleSample)
            );
        }
        if (!empty($options['link'])) {
            $options['link'] = $this->uploadLinkFiles($options['link']);
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link'),
                $this->prepareDataForSave($this->dataLink, $options['link'])
            );
            $dataLink = $this->fillDataLink($options['link']);
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_title'),
                $this->prepareDataForSave($this->dataLinkTitle, $dataLink['title'])
            );
            if (count($dataLink['price'])) {
                $this->connection->insertOnDuplicate(
                    $this->_resource->getTableName('downloadable_link_price'),
                    $this->prepareDataForSave($this->dataLinkPrice, $dataLink['price'])
                );
            }
        }
        return $this;
    }

    /**
     * Get type parameters - file or url
     *
     * @param string $option
     * @return string
     */
    protected function getTypeByValue($option){
        $result = self::FILE_OPTION_VALUE;
        if (preg_match('/\bhttps?:\/\//i', $option)) {
            $result = self::URL_OPTION_VALUE;
        }
        return $result;
    }

    /*
     * Prepare string to array data sample
     *
     * @param string $rowCol
     * @param int $entityId
     * @return array
     */
    protected function prepareSampleData($rowCol, $entityId = null){
        $result = [];
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($options as $option){
            $result[] =  array_merge(
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
    protected function prepareLinkData($rowCol, $entityId = null){
        $result = [];
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($options as $option){
            $result[] = array_merge(
                $this->dataLink,
                ['product_id' => $entityId],
                $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option))
            );
        }
        return $result;
    }

    /**
     * Delete options products
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function deleteOptions(){
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_link'),
            $this->connection->quoteInto('product_id IN (?)', $this->productIds)
        );
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_sample'),
            $this->connection->quoteInto('product_id IN (?)', $this->productIds)
        );
        return $this;
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
                    $option['sample_type'] = $this->getTypeByValue($value);
                    $option['sample_' . $option['sample_type']] = $value;
                }
                if ($key == self::URL_OPTION_VALUE || $key == self::FILE_OPTION_VALUE){
                    $option['link_type'] = $key;
                }
                if ($key == 'downloads' && $value == 'unlimited'){
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
                if ($key == self::URL_OPTION_VALUE || $key == self::FILE_OPTION_VALUE){
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
     * Returns an object for upload a media files
     *
     * @param string $type
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getUploader($type)
    {
        if ($this->fileUploader === null) {
            $this->fileUploader = $this->uploaderFactory->create();
            $this->fileUploader->init();
            $this->fileUploader->setAllowedExtensions($this->getAllowedExtensions());
            $this->fileUploader->removeValidateCallback('catalog_product_image');
        }
        $dirConfig = DirectoryList::getDefaultConfig();
        $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

        $DS = DIRECTORY_SEPARATOR;

        if (!empty($this->parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR])) {
            $tmpPath = $this->parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR];
        } else {
            $tmpPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath('import');
        }

        if (!$this->fileUploader->setTmpDir($tmpPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File directory \'%1\' is not readable.', $tmpPath)
            );
        }
        $destinationDir = "downloadable/files/" . $type;
        $destinationPath = $dirAddon . $DS . $this->mediaDirectory->getRelativePath($destinationDir);

        $this->mediaDirectory->create($destinationDir);
        if (!$this->fileUploader->setDestDir($destinationPath)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File directory \'%1\' is not writable.', $destinationPath)
            );
        }
        return $this->fileUploader;
    }

    /**
     * Uploading files into the "downloadable/files" media folder.
     * Return a new file name if the same file is already exists.
     * @param string $fileName
     * @param string $type
     * @return string
     */
    protected function uploadDownloadableFiles($fileName, $type = 'links', $renameFileOff = false)
    {
        try {
            $res = $this->getUploader($type)->move($fileName, $renameFileOff);
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
        $this->cachedOptions = [];
        $this->productIds = [];
        return $this;
    }

    /**
     * Get all allowed extensions
     *
     * @return array
     */
    protected function getAllowedExtensions(){
        $result = [];
        foreach(array_keys($this->fileHelper->getAllMineTypes()) as $option){
            $result[] = substr($option, 1);
        }
        return $result;
    }
}
