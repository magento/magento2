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
    protected $_mediaDirectory;
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;
    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_fileUploader;
    /**
     * Entity model parameters.
     *
     * @var array
     */
    protected $_parameters = [];
    /**
     * Instance of database adapter.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;
    /**
     * Instance of application resource.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;
    /**
     * Ids products
     *
     * @var array
     */
    protected $_productIds = [];
    /**
     * Id product
     *
     * @var int
     */
    protected $_productId;
    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $_cachedOptions = [];
    /**
     * Instance of empty sample
     *
     * @var array
     */
    protected $_dataSample = [
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
    protected $_dataSampleTitle = [
        'sample_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => null
    ];
    /**
     * Instance of empty link title
     *
     * @var array
     */
    protected $_dataLinkTitle = [
        'link_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => null
    ];
    /**
     * Instance of empty link price
     *
     * @var array
     */
    protected $_dataLinkPrice = [
        'link_id' => null,
        'price' => null
    ];
    /**
     * Instance of empty link
     *
     * @var array
     */

    protected $_dataLink = [
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
     * Option link mapping.
     *
     * @var array
     */
    protected $_optionLinkMapping = [
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
    protected $_optionSampleMapping = [
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
        $this->_uploaderFactory = $uploaderFactory;
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->_parameters = $this->_entityModel->getParameters();
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection('write');
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
            if (!empty($this->_cachedOptions)) {
                if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE) {
                    $this->deleteOptions();
                }
                $this->saveOptions($this->_cachedOptions);
                $this->clear();
            }
        }
    }
    /*
     *
     */
    public function parseOptions($rowData, $entityId){
        $this->_productId = $entityId;
        $options['link'][] =  $this->prepareLinkData($rowData['downloadble_links']);
        $options['sample'][] = $this->prepareSampleData($rowData['downloadble_samples']);
        $this->_cachedOptions[] = $options;
        $this->_productIds[] = $this->_productId;

    }
    /*
     *
     */
    public function getTitleSample($options){
        $_result = [];
        $existingOptions = $this->_connection->fetchAll(
            $this->_connection->select()->from(
                $this->_resource->getTableName('downloadable_sample')
            )->where(
                'product_id = ?',
                $this->_productId
            )
        );
        foreach($options as $option){
            foreach($existingOptions as $existingOption){
                if( $option['sample_url'] == $existingOption['sample_url'] &&
                    $option['sample_file'] == $existingOption['sample_file'] &&
                    $option['sample_type'] == $existingOption['sample_type']){
                    $_result[] = array_replace($this->_dataSampleTitle, $existingOption['sample_id']);
                }
            }

        }
        return $_result;
    }
    /*
     *
     */
    public function getDataLink($options){
        $_result = [];
        $existingOptions = $this->_connection->fetchAll(
            $this->_connection->select()->from(
                $this->_resource->getTableName('downloadable_link')
            )->where(
                'product_id = ?',
                $this->_productId
            )
        );
        foreach($options as $option){
            foreach($existingOptions as $existingOption){
                if( $option['link_url'] == $existingOption['link_url'] &&
                    $option['link_file'] == $existingOption['link_file'] &&
                    $option['link_type'] == $existingOption['link_type'] &&
                    $option['sample_url'] == $existingOption['sample_url'] &&
                    $option['sample_file'] == $existingOption['sample_file'] &&
                    $option['sample_type'] == $existingOption['sample_type']
                ){
                    $_result['title'][] = array_replace($this->_dataLinkTitle, $existingOption['link_id']);
                    $_result['price'][] = array_replace($this->_dataLinkPrice, $existingOption['link_id']);
                }
            }

        }
        return $_result;

    }
    /*
     *
     */
    public function getDownloadableSampleData($options){
        $_result = [];
        foreach($options as $option){
            unset($option['title']);
            unset($option['group_title']);
            $_result[] = $option;
        }
        return $_result;
    }
    /*
     *
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
     *
     */
    public function saveOptions($options){

        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample'),
            $this->getNormalData($this->_dataSample, $options['sample'])
        );
        $_titleSample = $this->getTitleSample($options['sample']);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample_title'),
            $this->getNormalData($this->_dataSampleTitle, $_titleSample)
        );
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link'),
            $this->getNormalData($this->_dataLink, $options['link'])
        );
        $_dataLink = $this->getDataLink($options['link']);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link_title'),
            $this->getNormalData($this->_dataLinkTitle, $_dataLink['title'])
        );
        if (count($_dataLink['price'])) {
            $this->_connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_price'),
                $this->getNormalData($this->_dataLinkPrice, $_dataLink['price'])
            );
        }
        return $this;

    }
    /*
     *
     */
    protected function checkTypeOption($type){
        $_result = 'file';
        if (preg_match('/\bhttps?:\/\//i', $type)) {
            $_result = 'url';
        }
        return $_result;
    }

    /*
     *
     */
    public function prepareSampleData($rowCol){
        $_result = [];
        $_options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($_options as $_option){
            $_result[] = array_merge(
                $this->_dataSample,
                ['product_id' => $this->_productId],
                $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $_option))
            );
        }
        return $_result;
    }
    /*
     *
     */
    public function prepareLinkData($rowCol){
        $_result = [];
        $_options = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        foreach($_options as $_option){
            $_result[] = array_merge(
                $this->_dataLink,
                ['product_id' => $this->_productId],
                $this->parseLinkOption(explode($this->_entityModel->getMultipleValueSeparator(), $_option))
            );
        }
        return $_result;
    }
    /*
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function deleteOptions(){
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_link'),
            $this->connection->quoteInto('product_id IN (?)', array_keys($this->_productIds))

        );
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_sample'),
            $this->connection->quoteInto('product_id IN (?)', array_keys($this->_productIds))
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
                        $value = $this->_uploadDownloadableFiles($value, 'link_samples', true);
                    }
                }
                if ($key == 'url' || $key == 'file'){
                    $option['link_type'] = $key;
                }
                if ($key == 'downloads' && $value == 'unlimited'){
                    $value = 0;
                }
                if (isset($this->_optionLinkMapping[$key])) {
                    $key = $this->_optionLinkMapping[$key];
                }

                if ($key == 'file'){
                    $value = $this->_uploadDownloadableFiles($value, 'links', true);
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
                if (isset($this->_optionSampleMapping[$key])) {
                    $key = $this->_optionSampleMapping[$key];
                }
                if ($key == 'file'){
                    $value = $this->_uploadDownloadableFiles($value, 'samples', true);
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
    protected function _getUploader($type)
    {
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = $this->_uploaderFactory->create();

            $this->_fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            if (!empty($this->_parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR])) {
                $tmpPath = $this->_parameters[\Magento\ImportExport\Model\Import::FIELD_NAME_IMG_FILE_DIR];
            } else {
                $tmpPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath('import');
            }

            if (!$this->_fileUploader->setTmpDir($tmpPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "downloadable/files/" . $type;
            $destinationPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath($destinationDir);

            $this->_mediaDirectory->create($destinationDir);
            if (!$this->_fileUploader->setDestDir($destinationPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->_fileUploader;
    }

    /**
     * Uploading files into the "downloadable/files" media folder.
     * Return a new file name if the same file is already exists.
     * @param string $fileName
     * @param string $type
     * @return string
     */
    protected function _uploadDownloadableFiles($fileName, $type = 'links', $renameFileOff = false)
    {
        try {
            $res = $this->_getUploader($type)->move($fileName, $renameFileOff);
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
        $this->_cachedOptions = [];
        $this->_productIds = [];
        return $this;
    }

}