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
     * Instance of application resource.
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $_productId;

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
                $this->_productId = $productData['entity_id'];
                if ($this->_entityModel->getBehavior() == \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE) {
                    $this->deleteOptions($this->_productId);
                }
                $_dataLink = array_merge(
                    $this->_dataLink,
                    $this->parseLinkOption($this->prepareData($rowData['downloadble_links']))
                );
                $_dataSample = array_merge(
                    $this->_dataSample,
                    $this->parseSampleOption($this->prepareData($rowData['downloadble_samples']))
                );
                $this->saveOptions($_dataLink, $_dataSample);
                $this->clear();
            }
        }
    }
    public function saveOptions($_dataLink, $_dataSample){
        $sampleTable = $this->_resource->getTableName('downloadable_sample');
        $linkTable = $this->_resource->getTableName('downloadable_link');
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample'),
            $_dataSample
        );
        //get sample id
        $_dataSample['sample_id'] = $this->_connection->lastInsertId($sampleTable);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_sample_title'),
            $_dataSample
        );
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link'),
            $_dataLink
        );
        $_dataLink['link_id'] = $this->_connection->lastInsertId($linkTable);
        $this->_connection->insertOnDuplicate(
            $this->_resource->getTableName('downloadable_link_title'),
            $_dataLink
        );
        if (isset($option['price'])) {
            $this->_connection->insertOnDuplicate(
                $this->_resource->getTableName('downloadable_link_price'),
                $_dataLink
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
    public function prepareData($rowCol){
        $downloadbleOptions = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowCol
        );
        return array_merge(
            ['product_id' => $this->_productId],
            explode($this->_entityModel->getMultipleValueSeparator(), $downloadbleOptions)
        );

    }
    /*
     * @param int $productId
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    public function deleteOptions($productId){
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_link'),
            ['product_id' => $productId]
        );
        $this->connection->delete(
            $this->_resource->getTableName('downloadable_sample'),
            ['product_id' => $productId]
        );
        return $this;
    }
    /*
     * @param array $rowData
     * @return Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable
     */
    protected function addSamples($rowData){
        $newSku = $this->_entityModel->getNewSku();
        $sampleTable = $this->_resource->getTableName('downloadable_sample');
        $sampleTitleTable = $this->_resource->getTableName('downloadable_sample_title');
        $downloadbleSamples = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowData['downloadble_samples']
        );
        $values = explode($this->_entityModel->getMultipleValueSeparator(), $downloadbleSamples);
        $option = $this->parseOption($values);
        $file = null;
        $dataSample = $this->_dataSample;
        if (isset($option['file'])){
            $dataSample['sample_file'] = $this->_uploadDownloadableFiles($option['file'], 'samples', true);
            $dataSample['sample_type'] = 'file';
        }
        if (isset($option['url'])){
            $dataSample['sample_url'] = $option['url'];
            $dataSample['sample_type'] = 'url';
        }
        $dataSample['product_id'] = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]]['entity_id'];
        $dataSample['sort_order'] = $option['sortorder'];
        $this->_connection->insertOnDuplicate($sampleTable, $dataSample);
        $lastInsertId = $this->_connection->lastInsertId($sampleTable);
        $dataTitle = [
            'title' => $option['title'],
            'sample_id' => $lastInsertId,
            'store_id' => '0'
        ];
        $this->_connection->insertOnDuplicate($sampleTitleTable, $dataTitle);
        return $this;
    }
    /*
     * @param array $rowData
     * @return Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable
     *
     */
    protected function addLinks($rowData){
        $newSku = $this->_entityModel->getNewSku();
        $linkTable = $this->_resource->getTableName('downloadable_link');
        $linkPriceTable = $this->_resource->getTableName('downloadable_link_price');
        $linkTitleTable = $this->_resource->getTableName('downloadable_link_title');

        $dataLink = $this->_dataLink;
        $downloadbleLinks = explode(
            \Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR,
            $rowData['downloadble_links']
        );
        $values = explode($this->_entityModel->getMultipleValueSeparator(), $downloadbleLinks);
        $option = $this->parseOption($values);
        if (isset($option['file'])){
            $dataLink['link_file'] = $this->_uploadDownloadableFiles($option['file'], 'links', true);
            $dataLink['link_type'] = 'file';
        }
        if (isset($option['url'])){
            $dataLink['link_url'] = $option['url'];
            $dataLink['link_type'] = 'url';
        }
        if (isset($option['sample_file'])){
            $dataLink['sample_file'] = $this->_uploadDownloadableFiles($option['sample_file'], 'link_samples', true);
            $dataLink['sample_type'] = 'file';
        }
        if (isset($option['sample_url'])){
            $dataLink['sample_url'] = $option['sample_url'];
            $dataLink['sample_type'] = 'url';
        }
        if (isset($option['sortorder'])){
            $dataLink['sort_order'] = $option['sortorder'];
        }
        $dataLink['product_id'] = $newSku[$rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_SKU]]['entity_id'];
        $this->_connection->insertOnDuplicate($linkTable, $dataLink);
        $lastInsertId = $this->_connection->lastInsertId($linkTable);
        $dataTitle = [
            'title' => $option['title'],
            'link_id' => $lastInsertId,
            'store_id' => '0'
        ];
        $this->_connection->insertOnDuplicate($linkTitleTable, $dataTitle);
        if (isset($option['price'])) {
            $dataPrice = [
                'title' => $option['price'],
                'link_id' => $lastInsertId,
                'website_id' => '0'
            ];
            $this->_connection->insertOnDuplicate($linkPriceTable, $dataPrice);
        }
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
                }
                if ($key == 'url' || $key == 'file'){
                    $option['link_type'] = $key;
                }
                if (isset($this->_optionLinkMapping[$key])) {
                    $key = $this->_optionLinkMapping[$key];
                }
                if ($key == 'file'){
                    $value = $this->_uploadDownloadableFiles($value, 'links', true);
                }
                if ($option['sample_type'] == 'file'){
                    $value = $this->_uploadDownloadableFiles($value, 'link_samples', true);
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
        $this->_cachedOptionSelectQuery = [];
        $this->_cachedSkus = [];
        $this->_cachedSkuToProducts = [];
        return $this;
    }

}