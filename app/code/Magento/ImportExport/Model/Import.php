<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Model;

use Magento\HTTP\Adapter\FileTransferFactory;

/**
 * Import model
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method string getBehavior() getBehavior()
 * @method \Magento\ImportExport\Model\Import setEntity() setEntity(string $value)
 */
class Import extends \Magento\ImportExport\Model\AbstractModel
{
    /**#@+
     * Import behaviors
     */
    const BEHAVIOR_APPEND = 'append';

    const BEHAVIOR_ADD_UPDATE = 'add_update';

    const BEHAVIOR_REPLACE = 'replace';

    const BEHAVIOR_DELETE = 'delete';

    const BEHAVIOR_CUSTOM = 'custom';

    /**#@-*/

    /**#@+
     * Form field names (and IDs)
     */
    const FIELD_NAME_SOURCE_FILE = 'import_file';

    const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**#@-*/

    /**#@+
     * Import constants
     */
    const DEFAULT_SIZE = 50;

    const MAX_IMPORT_CHUNKS = 4;

    /**#@-*/

    /**
     * Entity adapter.
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity
     */
    protected $_entityAdapter;

    /**
     * Entity invalidated indexes.
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEntity
     */
    protected static $_entityInvalidatedIndexes = array(
        'catalog_product' => array('catalog_product_price', 'catalogsearch_fulltext', 'catalog_product_flat')
    );

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface
     */
    protected $_importConfig;

    /**
     * @var \Magento\ImportExport\Model\Import\Entity\Factory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\ImportExport\Model\Resource\Import\Data
     */
    protected $_importData;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory
     */
    protected $_csvFactory;

    /**
     * @var \Magento\HTTP\Adapter\FileTransferFactory
     */
    protected $_httpFactory;

    /**
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @var \Magento\ImportExport\Model\Source\Import\Behavior\Factory
     */
    protected $_behaviorFactory;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Logger\AdapterFactory $adapterFactory
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param \Magento\ImportExport\Model\Import\ConfigInterface $importConfig
     * @param \Magento\ImportExport\Model\Import\Entity\Factory $entityFactory
     * @param \Magento\ImportExport\Model\Resource\Import\Data $importData
     * @param \Magento\ImportExport\Model\Export\Adapter\CsvFactory $csvFactory
     * @param \Magento\HTTP\Adapter\FileTransferFactory $httpFactory
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory
     * @param \Magento\Index\Model\Indexer $indexer
     * @param array $data
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Filesystem $filesystem,
        \Magento\Logger\AdapterFactory $adapterFactory,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\App\ConfigInterface $coreConfig,
        \Magento\ImportExport\Model\Import\ConfigInterface $importConfig,
        \Magento\ImportExport\Model\Import\Entity\Factory $entityFactory,
        \Magento\ImportExport\Model\Resource\Import\Data $importData,
        \Magento\ImportExport\Model\Export\Adapter\CsvFactory $csvFactory,
        \Magento\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory,
        \Magento\Index\Model\Indexer $indexer,
        array $data = array()
    ) {
        $this->_importExportData = $importExportData;
        $this->_coreConfig = $coreConfig;
        $this->_importConfig = $importConfig;
        $this->_entityFactory = $entityFactory;
        $this->_importData = $importData;
        $this->_csvFactory = $csvFactory;
        $this->_httpFactory = $httpFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_indexer = $indexer;
        $this->_behaviorFactory = $behaviorFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($logger, $filesystem, $adapterFactory, $data);
    }

    /**
     * Create instance of entity adapter and return it
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\ImportExport\Model\Import\Entity\AbstractEntity|\Magento\ImportExport\Model\Import\AbstractEntity
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $entities = $this->_importConfig->getEntities();

            if (isset($entities[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = $this->_entityFactory->create($entities[$this->getEntity()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                    throw new \Magento\Model\Exception(__('Please enter a correct entity model'));
                }
                if (!$this->_entityAdapter instanceof \Magento\ImportExport\Model\Import\Entity\AbstractEntity &&
                    !$this->_entityAdapter instanceof \Magento\ImportExport\Model\Import\AbstractEntity
                ) {
                    throw new \Magento\Model\Exception(
                        __(
                            'Entity adapter object must be an instance of %1 or %2',
                            'Magento\ImportExport\Model\Import\Entity\AbstractEntity',
                            'Magento\ImportExport\Model\Import\AbstractEntity'
                        )
                    );
                }

                // check for entity codes integrity
                if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                    throw new \Magento\Model\Exception(
                        __('The input entity code is not equal to entity adapter code.')
                    );
                }
            } else {
                throw new \Magento\Model\Exception(__('Please enter a correct entity.'));
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }

    /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return \Magento\ImportExport\Model\Import\AbstractSource
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return \Magento\ImportExport\Model\Import\Adapter::findAdapterFor(
            $sourceFile,
            $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR)
        );
    }

    /**
     * Return operation result messages
     *
     * @param bool $validationResult
     * @return string[]
     */
    public function getOperationResultMessages($validationResult)
    {
        $messages = array();
        if ($this->getProcessedRowsCount()) {
            if (!$validationResult) {
                if ($this->getProcessedRowsCount() == $this->getInvalidRowsCount()) {
                    $messages[] = __('File is totally invalid. Please fix errors and re-upload file.');
                } elseif ($this->getErrorsCount() >= $this->getErrorsLimit()) {
                    $messages[] = __(
                        'Errors limit (%1) reached. Please fix errors and re-upload file.',
                        $this->getErrorsLimit()
                    );
                } else {
                    if ($this->isImportAllowed()) {
                        $messages[] = __('Please fix errors and re-upload file.');
                    } else {
                        $messages[] = __('File is partially valid, but import is not possible');
                    }
                }
                // errors info
                foreach ($this->getErrors() as $errorCode => $rows) {
                    $error = $errorCode . ' ' . __('in rows') . ': ' . implode(', ', $rows);
                    $messages[] = $error;
                }
            } else {
                if ($this->isImportAllowed()) {
                    $messages[] = __('Validation finished successfully');
                } else {
                    $messages[] = __('File is valid, but import is not possible');
                }
            }
            $notices = $this->getNotices();
            if (is_array($notices)) {
                $messages = array_merge($messages, $notices);
            }
            $messages[] = __(
                'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                $this->getProcessedRowsCount(),
                $this->getProcessedEntitiesCount(),
                $this->getInvalidRowsCount(),
                $this->getErrorsCount()
            );
        } else {
            $messages[] = __('File does not contain data.');
        }
        return $messages;
    }

    /**
     * Get attribute type for upcoming validation.
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\Magento\Eav\Model\Entity\Attribute $attribute
     * @return string
     */
    public static function getAttributeType(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute)
    {
        if ($attribute->usesSource()) {
            return $attribute->getFrontendInput() == 'multiselect' ? 'multiselect' : 'select';
        } elseif ($attribute->isStatic()) {
            return $attribute->getFrontendInput() == 'date' ? 'datetime' : 'varchar';
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * DB data source model getter.
     *
     * @return \Magento\ImportExport\Model\Resource\Import\Data
     */
    public function getDataSourceModel()
    {
        return $this->_importData;
    }

    /**
     * Default import behavior getter.
     *
     * @static
     * @return string
     */
    public static function getDefaultBehavior()
    {
        return self::BEHAVIOR_APPEND;
    }

    /**
     * Override standard entity getter.
     *
     * @throws \Magento\Model\Exception
     * @return string
     */
    public function getEntity()
    {
        if (empty($this->_data['entity'])) {
            throw new \Magento\Model\Exception(__('Entity is unknown'));
        }
        return $this->_data['entity'];
    }

    /**
     * Get entity adapter errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_getEntityAdapter()->getErrorMessages();
    }

    /**
     * Returns error counter.
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->_getEntityAdapter()->getErrorsCount();
    }

    /**
     * Returns error limit value.
     *
     * @return int
     */
    public function getErrorsLimit()
    {
        return $this->_getEntityAdapter()->getErrorsLimit();
    }

    /**
     * Returns invalid rows count.
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return $this->_getEntityAdapter()->getInvalidRowsCount();
    }

    /**
     * Returns entity model noticees.
     *
     * @return string[]
     */
    public function getNotices()
    {
        return $this->_getEntityAdapter()->getNotices();
    }

    /**
     * Returns number of checked entities.
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_getEntityAdapter()->getProcessedEntitiesCount();
    }

    /**
     * Returns number of checked rows.
     *
     * @return int
     */
    public function getProcessedRowsCount()
    {
        return $this->_getEntityAdapter()->getProcessedRowsCount();
    }

    /**
     * Import/Export working directory (source files, result files, lock files etc.).
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->_varDirectory->getAbsolutePath('importexport/');
    }

    /**
     * Import source file structure to DB.
     *
     * @return bool
     */
    public function importSource()
    {
        $this->setData(
            array(
                'entity' => $this->getDataSourceModel()->getEntityTypeCode(),
                'behavior' => $this->getDataSourceModel()->getBehavior()
            )
        );

        $this->addLogComment(__('Begin import of "%1" with "%2" behavior', $this->getEntity(), $this->getBehavior()));

        $result = $this->_getEntityAdapter()->importData();

        $this->addLogComment(
            array(
                __(
                    'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                    $this->getProcessedRowsCount(),
                    $this->getProcessedEntitiesCount(),
                    $this->getInvalidRowsCount(),
                    $this->getErrorsCount()
                ),
                __('Import has been done successfuly.')
            )
        );

        return $result;
    }

    /**
     * Import possibility getter.
     *
     * @return bool
     */
    public function isImportAllowed()
    {
        return $this->_getEntityAdapter()->isImportAllowed();
    }

    /**
     * Import source file structure to DB.
     *
     * @return void
     */
    public function expandSource()
    {
        /** @var $writer \Magento\ImportExport\Model\Export\Adapter\Csv */
        $writer = $this->_csvFactory->create(array('destination' => $this->getWorkingDir() . "big0.csv"));
        $regExps = array('last' => '/(.*?)(\d+)$/', 'middle' => '/(.*?)(\d+)(.*)$/');
        $colReg = array(
            'sku' => 'last',
            'name' => 'last',
            'description' => 'last',
            'short_description' => 'last',
            'url_key' => 'middle',
            'meta_title' => 'last',
            'meta_keyword' => 'last',
            'meta_description' => 'last',
            '_links_related_sku' => 'last',
            '_links_crosssell_sku' => 'last',
            '_links_upsell_sku' => 'last',
            '_custom_option_sku' => 'middle',
            '_custom_option_row_sku' => 'middle',
            '_super_products_sku' => 'last',
            '_associated_sku' => 'last'
        );
        $size = self::DEFAULT_SIZE;

        $filename = 'catalog_product.csv';
        $filenameFormat = 'big%s.csv';
        foreach ($this->_getSourceAdapter($this->getWorkingDir() . $filename) as $row) {
            $writer->writeRow($row);
        }
        $count = self::MAX_IMPORT_CHUNKS;
        for ($i = 1; $i < $count; $i++) {
            $writer = $this->_csvFactory->create(
                array('destination' => $this->getWorkingDir() . sprintf($filenameFormat, $i))
            );

            $adapter = $this->_getSourceAdapter($this->getWorkingDir() . sprintf($filenameFormat, $i - 1));
            foreach ($adapter as $row) {
                $writer->writeRow($row);
            }
            $adapter = $this->_getSourceAdapter($this->getWorkingDir() . sprintf($filenameFormat, $i - 1));
            foreach ($adapter as $row) {
                foreach ($colReg as $colName => $regExpType) {
                    if (!empty($row[$colName])) {
                        preg_match($regExps[$regExpType], $row[$colName], $matches);

                        $row[$colName] = $matches[1] . ($matches[2] + $size) . ('middle' ==
                            $regExpType ? $matches[3] : '');
                    }
                }
                $writer->writeRow($row);
            }
            $size *= 2;
        }
    }

    /**
     * Move uploaded file and create source adapter instance.
     *
     * @throws \Magento\Model\Exception
     * @return string Source file path
     */
    public function uploadSource()
    {
        /** @var $adapter \Zend_File_Transfer_Adapter_Http */
        $adapter = $this->_httpFactory->create();
        if (!$adapter->isValid(self::FIELD_NAME_SOURCE_FILE)) {
            $errors = $adapter->getErrors();
            if ($errors[0] == \Zend_Validate_File_Upload::INI_SIZE) {
                $errorMessage = $this->_importExportData->getMaxUploadSizeMessage();
            } else {
                $errorMessage = __('File was not uploaded.');
            }
            throw new \Magento\Model\Exception($errorMessage);
        }

        $entity = $this->getEntity();
        /** @var $uploader \Magento\Core\Model\File\Uploader */
        $uploader = $this->_uploaderFactory->create(array('fileId' => self::FIELD_NAME_SOURCE_FILE));
        $uploader->skipDbProcessing(true);
        $result = $uploader->save($this->getWorkingDir());
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);

        $uploadedFile = $result['path'] . $result['file'];
        if (!$extension) {
            $this->_varDirectory->delete($uploadedFile);
            throw new \Magento\Model\Exception(__('Uploaded file has no extension'));
        }
        $sourceFile = $this->getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;
        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($this->_varDirectory->isExist($sourceFileRelative)) {
                $this->_varDirectory->delete($sourceFileRelative);
            }

            try {
                $this->_varDirectory->renameFile(
                    $this->_varDirectory->getRelativePath($uploadedFile),
                    $sourceFileRelative
                );
            } catch (\Magento\Filesystem\FilesystemException $e) {
                throw new \Magento\Model\Exception(__('Source file moving failed'));
            }
        }
        $this->_removeBom($sourceFile);
        // trying to create source adapter for file and catch possible exception to be convinced in its adequacy
        try {
            $this->_getSourceAdapter($sourceFile);
        } catch (\Exception $e) {
            $this->_varDirectory->delete($sourceFileRelative);
            throw new \Magento\Model\Exception($e->getMessage());
        }
        return $sourceFile;
    }

    /**
     * Remove BOM from a file
     *
     * @param string $sourceFile
     * @return $this
     */
    protected function _removeBom($sourceFile)
    {
        $string = $this->_varDirectory->readFile($this->_varDirectory->getRelativePath($sourceFile));
        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            $this->_varDirectory->writeFile($this->_varDirectory->getRelativePath($sourceFile), $string);
        }
        return $this;
    }

    /**
     * Validates source file and returns validation result.
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     * @return bool
     */
    public function validateSource(\Magento\ImportExport\Model\Import\AbstractSource $source)
    {
        $this->addLogComment(__('Begin data validation'));
        $adapter = $this->_getEntityAdapter()->setSource($source);
        $result = $adapter->isDataValid();

        $messages = $this->getOperationResultMessages($result);
        $this->addLogComment($messages);
        if ($result) {
            $this->addLogComment(__('Done import data validation'));
        }
        return $result;
    }

    /**
     * Invalidate indexes by process codes.
     *
     * @return $this
     */
    public function invalidateIndex()
    {
        if (!isset(self::$_entityInvalidatedIndexes[$this->getEntity()])) {
            return $this;
        }

        $indexers = self::$_entityInvalidatedIndexes[$this->getEntity()];
        foreach ($indexers as $indexer) {
            $indexProcess = $this->_indexer->getProcessByCode($indexer);
            if ($indexProcess) {
                $indexProcess->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
            }
        }

        return $this;
    }

    /**
     * Gets array of customer entities and appropriate behaviours
     * array(
     *     <entity_code> => array(
     *         'token' => <behavior_class_name>,
     *         'code'  => <behavior_model_code>,
     *     ),
     *     ...
     * )
     *
     * @return array
     * @throws \Magento\Model\Exception
     */
    public function getEntityBehaviors()
    {
        $behaviourData = array();
        $entities = $this->_importConfig->getEntities();
        foreach ($entities as $entityCode => $entityData) {
            $behaviorClassName = isset($entityData['behaviorModel']) ? $entityData['behaviorModel'] : null;
            if ($behaviorClassName && class_exists($behaviorClassName)) {
                /** @var $behavior \Magento\ImportExport\Model\Source\Import\AbstractBehavior */
                $behavior = $this->_behaviorFactory->create($behaviorClassName);
                $behaviourData[$entityCode] = array(
                    'token' => $behaviorClassName,
                    'code' => $behavior->getCode() . '_behavior'
                );
            } else {
                throw new \Magento\Model\Exception(__('Invalid behavior token for %1', $entityCode));
            }
        }
        return $behaviourData;
    }

    /**
     * Get array of unique entity behaviors
     * array(
     *     <behavior_model_code> => <behavior_class_name>,
     *     ...
     * )
     *
     * @return array
     */
    public function getUniqueEntityBehaviors()
    {
        $uniqueBehaviors = array();
        $behaviourData = $this->getEntityBehaviors();
        foreach ($behaviourData as $behavior) {
            $behaviorCode = $behavior['code'];
            if (!isset($uniqueBehaviors[$behaviorCode])) {
                $uniqueBehaviors[$behaviorCode] = $behavior['token'];
            }
        }
        return $uniqueBehaviors;
    }
}
