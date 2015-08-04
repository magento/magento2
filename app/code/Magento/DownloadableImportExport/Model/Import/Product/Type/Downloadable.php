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
     * Instance of database adapter.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Instance of application resource.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

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
        'product_id' => 0,
        'sample_url' => null,
        'sample_file' => null,
        'sample_type' => null,
        'sort_order' => 0
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
        'product_id' => 0,
        'sort_order' => 0,
        'number_of_downloads' => 0,
        'is_shareable' => 2,
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

    /*
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param array $params
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
        $this->resource = $resource;
        $this->connection = $resource->getConnection('write');
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
    protected function getGroupTitle($rowData)
    {
        $resultAttrs = [
            'samples_title' => '',
            'links_title' => ''
        ];
        if (isset($rowData['downloadble_samples'])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData['downloadble_samples']
            );
            foreach ($options as $option) {
                $arr = $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr['group_title'])){
                    $resultAttrs['samples_title'] = $arr['group_title'];
                    break;
                }
            }
        }
        if (isset($rowData['downloadble_links'])) {
            $options = explode(
                \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData['downloadble_links']
            );
            foreach($options as $option){
                $arr = $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (isset($arr['group_title'])){
                    $resultAttrs['links_title'] = $arr['group_title'];
                    break;
                }
            }
        }
        return $resultAttrs;
    }

    /*
     * Parse options for products
     *
     * @param array $values
     */
    public function parseOptions($rowData, $entityId){
        $this->productIds[] = $entityId;
        $this->prepareLinkData($rowData['downloadble_links'], $entityId);
        $this->prepareSampleData($rowData['downloadble_samples'], $entityId);
    }

    /*
     * Get fill data options with key sample
     *
     * @param array $options
     * @return array
     */
    public function getTitleSample($options){
        $_result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                $this->resource->getTableName('downloadable_sample')
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
                    $_result[] = array_replace($this->dataSampleTitle, $option, $existingOption);
                }
            }

        }
        return $_result;
    }

    /*
     * Get fill data options with key link
     *
     * @param array $options
     * @return array
     */
    public function getDataLink($options){
        $_result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->_connection->select()->from(
                $this->resource->getTableName('downloadable_link')
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
                    $_result['title'][] = array_replace($this->dataLinkTitle, $option, $existingOption);
                    $_result['price'][] = array_replace($this->dataLinkPrice, $option, $existingOption);
                }
            }
        }
        return $_result;
    }

    /*
     * Fill array data options for base entity
     *
     * @param array $base
     * @param array $replacement
     * @return array
     */
    public function getNormalData($base, $replacement){
        $_result = [];
        $keys = array_intersect_key($base, current($replacement));
        foreach($replacement as $_item){
            foreach($keys as $key => $value){
                $base[$key] = $_item[$key];
            }
            $_result[] = $base;
        }
        return $_result;
    }

    /*
     * Save options in base
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function saveOptions(){
        $options = $this->cachedOptions;
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample'),
            $this->getNormalData($this->dataSample, $options['sample'])
        );
        $_titleSample = $this->getTitleSample($options['sample']);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample_title'),
            $this->getNormalData($this->dataSampleTitle, $_titleSample)
        );
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link'),
            $this->getNormalData($this->dataLink, $options['link'])
        );
        $_dataLink = $this->getDataLink($options['link']);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link_title'),
            $this->getNormalData($this->dataLinkTitle, $_dataLink['title'])
        );
        if (count($_dataLink['price'])) {
            $this->_connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_price'),
                $this->getNormalData($this->dataLinkPrice, $_dataLink['price'])
            );
        }
        return $this;
    }

    /*
     * Check type parameters - file or url
     *
     * @param string $type
     * @return string
     */
    protected function checkTypeOption($type){
        $_result = 'file';
        if (preg_match('/\bhttps?:\/\//i', $type)) {
            $_result = 'url';
        }
        return $_result;
    }

    /*
     * Prepare string to array data sample
     *
     * @param string $rowCol
     * @param string $entityId
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function prepareSampleData($rowCol, $entityId){
        $_options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($_options as $_option){
            $this->cachedOptions['sample'][] =  array_merge(
                $this->dataSample,
                ['product_id' => $entityId],
                $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $_option))
            );
        }
        return $this;
    }

    /*
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

    /*
     * Delete options products
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function deleteOptions(){
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_link'),
            $this->_connection->quoteInto('product_id IN (?)', $this->productIds)
        );
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_sample'),
            $this->_connection->quoteInto('product_id IN (?)', $this->productIds)
        );
        return $this;
    }

    /**
     * Parse the link option.
     *
     * @param array $values
     *
     * @return array
     */
    protected function parseLinkOption($values)
    {
        $option = [];
        foreach ($values as $keyValue) {
            $keyValue = trim($keyValue);
            if ($pos = strpos($keyValue, self::PAIR_VALUE_SEPARATOR)) {
                $key = substr($keyValue, 0, $pos);
                $value = substr($keyValue, $pos + 1);
                if ($key == 'sample') {
                    $option['sample_type'] = $this->checkTypeOption($value);
                    $option['sample_' . $option['sample_type']] = $value;
                    if ($option['sample_type'] == 'file'){
                        $value = $this->uploadDownloadableFiles($value, 'link_samples', true);
                    }
                }
                if ($key == 'url' || $key == 'file'){
                    $option['link_type'] = $key;
                }
                if ($key == 'downloads' && $value == 'unlimited'){
                    $value = 0;
                }
                if (isset($this->optionLinkMapping[$key])) {
                    $key = $this->optionLinkMapping[$key];
                }
                if ($key == 'file'){
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
     *
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
                if ($key == 'url' || $key == 'file'){
                    $option['sample_type'] = $key;
                }
                if (isset($this->optionSampleMapping[$key])) {
                    $key = $this->optionSampleMapping[$key];
                }
                if ($key == 'file'){
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