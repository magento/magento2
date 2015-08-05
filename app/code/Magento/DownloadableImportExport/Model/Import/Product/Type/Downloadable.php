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
    protected $cachedOptions = [];

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
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\Resource $resource
     * @param array $params
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\Resource $resource,
        array $params,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
    ){
        $this->uploaderFactory = $uploaderFactory;
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->parameters = $this->_entityModel->getParameters();
        $this->_resource = $resource;
        $this->connection = $resource->getConnection('write');
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
                if (!empty($this->cachedOptions)) {
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
            if (!is_null($option['sample_file'])){
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
            if (!is_null($option['sample_file'])){
                unlink(self::DOWNLOADABLE_PATCH_LINK_SAMPLES . $option['sample_file']);
            }
            if (!is_null($option['link_file'])){
                unlink(self::DOWNLOADABLE_PATCH_LINKS . $option['link_file']);
            }
        }
        return $this;
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
        $resultAttrs = array_merge($resultAttrs, $this->getGroupTitle($rowData));
        return $resultAttrs;
    }

    /**
     * Get group title for dowloadable product.
     *
     * @param array $rowData
     * @return array
     */
    protected function getGroupTitle(array $rowData)
    {
        return [
            'samples_title' => $this->sampleGroupTitle($rowData),
            'links_title' => $this->linkGroupTitle($rowData)
        ];
    }

    /**
     * Get group title for sample
     *
     * @param array $rowData
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function sampleGroupTitle(array $rowData){
        $result = '';
        if (isset($rowData['downloadable_samples'])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData['downloadable_samples']
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
     * Get link group title
     *
     * @param array $rowData
     * @return string
     */
    protected function linkGroupTitle(array $rowData){
        $result = '';
        if (isset($rowData['downloadable_links'])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData['downloadable_samples']
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
    public function parseOptions(array $rowData, $entityId){
        $this->productIds[] = $entityId;
        $this->prepareLinkData($rowData['downloadble_links'], $entityId);
        $this->prepareSampleData($rowData['downloadble_samples'], $entityId);
        return $this;
    }

    /**
     * Get fill data options with key sample
     *
     * @param array $options
     * @return array
     */
    public function getTitleSample(array $options){
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
    public function getDataLink(array $options){
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
    public function getNormalData(array $base, array $replacement){
        $result = [];
        foreach($replacement as $item){
            $result[] = array_intersect_key($item, $base);
        }
        return $result;
    }

    /**
     * Save options in base
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveOptions(){
        $options = $this->cachedOptions;
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample'),
            $this->getNormalData($this->dataSample, $options['sample'])
        );
        $titleSample = $this->getTitleSample($options['sample']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample_title'),
            $this->getNormalData($this->dataSampleTitle, $titleSample)
        );
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link'),
            $this->getNormalData($this->dataLink, $options['link'])
        );
        $dataLink = $this->getDataLink($options['link']);
        $this->connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link_title'),
            $this->getNormalData($this->dataLinkTitle, $dataLink['title'])
        );
        if (count($dataLink['price'])) {
            $this->connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_price'),
                $this->getNormalData($this->dataLinkPrice, $dataLink['price'])
            );
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
     * @param string $entityId
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function prepareSampleData($rowCol, $entityId){
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($options as $option){
            $this->cachedOptions['sample'][] =  array_merge(
                $this->dataSample,
                ['product_id' => $entityId],
                $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option))
            );
        }
        return $this;
    }

    /**
     * Prepare string to array data link
     *
     * @param string $rowCol
     * @param string $entityId
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function prepareLinkData($rowCol, $entityId){
        $options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($options as $option){
            $this->cachedOptions['link'][] = array_merge(
                $this->dataLink,
                ['product_id' => $entityId],
                $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option))
            );
        }
        return $this;
    }

    /**
     * Delete options products
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function deleteOptions(){
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
                    if ($option['sample_type'] == self::FILE_OPTION_VALUE){
                        $value = $this->uploadDownloadableFiles($value, 'link_samples', true);
                    }
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
                if ($key == self::FILE_OPTION_VALUE){
                    $value = $this->uploadDownloadableFiles($value, 'links', true);
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
                if ($key == self::FILE_OPTION_VALUE){
                    $value = $this->uploadDownloadableFiles($value, 'samples', true);
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
        if (is_null($this->fileUploader)) {
            $this->_fileUploader = $this->uploaderFactory->create();

            $this->fileUploader->init();

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
        }
        return $this->fileUploader;
    }

    /**
     * Uploading files into the "downloadable/files" media folder.
     * Return a new file name if the same file is already exists.
     * @param string $fileName
     * @param string $type
     * @return string
     * @todo Requirement integrity with error processing
     */
    protected function uploadDownloadableFiles($fileName, $type = 'links', $renameFileOff = false)
    {
        try {
            $res = $this->getUploader($type)->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
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
}
