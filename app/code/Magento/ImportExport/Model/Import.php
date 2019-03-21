<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Import model
 *
 * @method string getBehavior() getBehavior()
 * @method self setEntity() setEntity(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Import extends AbstractModel
{
    const BEHAVIOR_APPEND = 'append';
    const BEHAVIOR_ADD_UPDATE = 'add_update';
    const BEHAVIOR_REPLACE = 'replace';
    const BEHAVIOR_DELETE = 'delete';
    const BEHAVIOR_CUSTOM = 'custom';
    const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * Import image archive.
     */
    const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**
     * Import images file directory.
     */
    const FIELD_NAME_IMG_FILE_DIR = 'import_images_file_dir';

    /**
     * Allowed errors count field name
     */
    const FIELD_NAME_ALLOWED_ERROR_COUNT = 'allowed_error_count';

    /**
     * Validation startegt field name
     */
    const FIELD_NAME_VALIDATION_STRATEGY = 'validation_strategy';

    /**
     * Import field separator.
     */
    const FIELD_FIELD_SEPARATOR = '_import_field_separator';

    /**
     * Import multiple value separator.
     */
    const FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR = '_import_multiple_value_separator';

    /**
     * Import empty attribute value constant.
     */
    const FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '_import_empty_attribute_value_constant';

    /**
     * Allow multiple values wrapping in double quotes for additional attributes.
     */
    const FIELDS_ENCLOSURE = 'fields_enclosure';

    /**
     * default delimiter for several values in one cell as default for FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR
     */
    const DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR = ',';

    /**
     * default empty attribute value constant
     */
    const DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '__EMPTY__VALUE__';
    const DEFAULT_SIZE = 50;
    const MAX_IMPORT_CHUNKS = 4;
    const IMPORT_HISTORY_DIR = 'import_history/';
    const IMPORT_DIR = 'import/';

    /**
     * Entity adapter.
     *
     * @var AbstractEntity
     */
    protected $_entityAdapter;

    /**
     * Import export data
     *
     * @var Data
     */
    protected $_importExportData = null;

    /**
     * @var ConfigInterface
     */
    protected $_importConfig;

    /**
     * @var Factory
     */
    protected $_entityFactory;

    /**
     * @var ImportData
     */
    protected $_importData;

    /**
     * @var CsvFactory
     */
    protected $_csvFactory;

    /**
     * @var FileTransferFactory
     */
    protected $_httpFactory;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var BehaviorFactory
     */
    protected $_behaviorFactory;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var History
     */
    private $importHistoryModel;

    /**
     * @var DateTime
     */
    private $localeDate;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param Data $importExportData
     * @param ScopeConfigInterface $coreConfig
     * @param Import\ConfigInterface $importConfig
     * @param Import\Entity\Factory $entityFactory
     * @param ImportData $importData
     * @param Export\Adapter\CsvFactory $csvFactory
     * @param FileTransferFactory $httpFactory
     * @param UploaderFactory $uploaderFactory
     * @param Source\Import\Behavior\Factory $behaviorFactory
     * @param IndexerRegistry $indexerRegistry
     * @param History $importHistoryModel
     * @param DateTime $localeDate
     * @param array $data
     * @param Random|null $random
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Filesystem $filesystem,
        Data $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        ImportData $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        array $data = [],
        Random $random = null
    ) {
        $this->_importExportData = $importExportData;
        $this->_coreConfig = $coreConfig;
        $this->_importConfig = $importConfig;
        $this->_entityFactory = $entityFactory;
        $this->_importData = $importData;
        $this->_csvFactory = $csvFactory;
        $this->_httpFactory = $httpFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->_behaviorFactory = $behaviorFactory;
        $this->_filesystem = $filesystem;
        $this->importHistoryModel = $importHistoryModel;
        $this->localeDate = $localeDate;
        $this->random = $random ?: ObjectManager::getInstance()
            ->get(Random::class);
        parent::__construct($logger, $filesystem, $data);
    }

    /**
     * Create instance of entity adapter and return it
     *
     * @throws LocalizedException
     * @return AbstractEntity|ImportAbstractEntity
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $entities = $this->_importConfig->getEntities();
            if (isset($entities[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = $this->_entityFactory->create($entities[$this->getEntity()]['model']);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    throw new LocalizedException(
                        __('Please enter a correct entity model.')
                    );
                }
                if (!$this->_entityAdapter instanceof AbstractEntity &&
                    !$this->_entityAdapter instanceof ImportAbstractEntity
                ) {
                    throw new LocalizedException(
                        __(
                            'The entity adapter object must be an instance of %1 or %2.',
                            AbstractEntity::class,
                            ImportAbstractEntity::class
                        )
                    );
                }

                // check for entity codes integrity
                if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                    throw new LocalizedException(
                        __('The input entity code is not equal to entity adapter code.')
                    );
                }
            } else {
                throw new LocalizedException(__('Please enter a correct entity.'));
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }

    /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return AbstractSource
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return Adapter::findAdapterFor(
            $sourceFile,
            $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT),
            $this->getData(self::FIELD_FIELD_SEPARATOR)
        );
    }

    /**
     * Return operation result messages
     *
     * @param ProcessingErrorAggregatorInterface $validationResult
     * @return string[]
     */
    public function getOperationResultMessages(ProcessingErrorAggregatorInterface $validationResult)
    {
        $messages = [];
        if ($this->getProcessedRowsCount()) {
            if ($validationResult->getErrorsCount()) {
                $messages[] = __('Data validation failed. Please fix the following errors and upload the file again.');

                // errors info
                foreach ($validationResult->getRowsGroupedByErrorCode() as $errorMessage => $rows) {
                    $error = $errorMessage . ' ' . __('in row(s)') . ': ' . implode(', ', $rows);
                    $messages[] = $error;
                }
            } else {
                if ($this->isImportAllowed()) {
                    $messages[] = __('The validation is complete.');
                } else {
                    $messages[] = __('The file is valid, but we can\'t import it for some reason.');
                }
            }

            $messages[] = __(
                'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                $this->getProcessedRowsCount(),
                $this->getProcessedEntitiesCount(),
                $validationResult->getInvalidRowsCount(),
                $validationResult->getErrorsCount(
                    [
                        ProcessingError::ERROR_LEVEL_CRITICAL,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    ]
                )
            );
        } else {
            $messages[] = __('This file does not contain any data.');
        }
        return $messages;
    }

    /**
     * Get attribute type for upcoming validation.
     *
     * @param AbstractAttribute|Attribute $attribute
     * @return string
     */
    public static function getAttributeType(AbstractAttribute $attribute)
    {
        $frontendInput = $attribute->getFrontendInput();
        if ($attribute->usesSource() && in_array($frontendInput, ['select', 'multiselect', 'boolean'])) {
            return $frontendInput;
        } elseif ($attribute->isStatic()) {
            return $frontendInput == 'date' ? 'datetime' : 'varchar';
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * DB data source model getter.
     *
     * @return ImportData
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
     * @throws LocalizedException
     * @return string
     */
    public function getEntity()
    {
        $entities = $this->_importConfig->getEntities();

        if (empty($this->_data['entity'])
            || !empty($this->_data['entity']) && !isset($entities[$this->_data['entity']])
        ) {
            throw new LocalizedException(__('Entity is unknown'));
        }

        return $this->_data['entity'];
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
     * @throws LocalizedException
     */
    public function importSource()
    {
        $this->setData('entity', $this->getDataSourceModel()->getEntityTypeCode());
        $this->setData('behavior', $this->getDataSourceModel()->getBehavior());
        $this->importHistoryModel->updateReport($this);

        $this->addLogComment(__('Begin import of "%1" with "%2" behavior', $this->getEntity(), $this->getBehavior()));

        $result = $this->processImport();

        if ($result) {
            $this->addLogComment(
                [
                    __(
                        'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                        $this->getProcessedRowsCount(),
                        $this->getProcessedEntitiesCount(),
                        $this->getErrorAggregator()->getInvalidRowsCount(),
                        $this->getErrorAggregator()->getErrorsCount()
                    ),
                    __('The import was successful.'),
                ]
            );
            $this->importHistoryModel->updateReport($this, true);
        } else {
            $this->importHistoryModel->invalidateReport($this);
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function processImport()
    {
        $errorAggregator = $this->_getEntityAdapter()->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->getData(self::FIELD_NAME_VALIDATION_STRATEGY),
            $this->getData(self::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );
        try {
            $this->_getEntityAdapter()->importData();
        } catch (\Exception $e) {
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                null,
                $e->getMessage()
            );
        }

        return !$errorAggregator->hasToBeTerminated();
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
     * @return ProcessingErrorAggregatorInterface
     * @throws LocalizedException
     */
    public function getErrorAggregator()
    {
        return $this->_getEntityAdapter()->getErrorAggregator();
    }

    /**
     * Move uploaded file and create source adapter instance.
     *
     * @throws LocalizedException
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
                $errorMessage = __('The file was not uploaded.');
            }
            throw new LocalizedException($errorMessage);
        }

        $entity = $this->getEntity();
        /** @var $uploader Uploader */
        $uploader = $this->_uploaderFactory->create(['fileId' => self::FIELD_NAME_SOURCE_FILE]);
        $uploader->skipDbProcessing(true);
        $fileName = $this->random->getRandomString(32) . '.' . $uploader->getFileExtension();
        $result = $uploader->save($this->getWorkingDir(), $fileName);
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);

        $uploadedFile = $result['path'] . $result['file'];
        if (!$extension) {
            $this->_varDirectory->delete($uploadedFile);
            throw new LocalizedException(__('The file you uploaded has no extension.'));
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
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('The source file moving process failed.'));
            }
        }
        $this->_removeBom($sourceFile);
        $this->createHistoryReport($sourceFileRelative, $entity, $extension, $result);

        return $sourceFile;
    }

    /**
     * Move uploaded file and provide source instance.
     *
     * @return Import\AbstractSource
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function uploadFileAndGetSource()
    {
        $sourceFile = $this->uploadSource();
        try {
            $source = $this->_getSourceAdapter($sourceFile);
        } catch (\Exception $e) {
            $this->_varDirectory->delete($this->_varDirectory->getRelativePath($sourceFile));
            throw new LocalizedException(__($e->getMessage()));
        }

        return $source;
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
     * Validates source file and returns validation result
     *
     * Before validate data the method requires to initialize error aggregator (ProcessingErrorAggregatorInterface)
     * with 'validation strategy' and 'allowed error count' values to allow using this parameters in validation process.
     *
     * @param AbstractSource $source
     * @return bool
     */
    public function validateSource(AbstractSource $source)
    {
        $this->addLogComment(__('Begin data validation'));

        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->getData(self::FIELD_NAME_VALIDATION_STRATEGY),
            $this->getData(self::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );

        try {
            $adapter = $this->_getEntityAdapter()->setSource($source);
            $adapter->validateData();
        } catch (\Exception $e) {
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                null,
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->addLogComment($messages);

        $result = !$errorAggregator->getErrorsCount();
        if ($result) {
            $this->addLogComment(__('Import data validation is complete.'));
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
        $relatedIndexers = $this->_importConfig->getRelatedIndexers($this->getEntity());
        if (empty($relatedIndexers)) {
            return $this;
        }

        foreach (array_keys($relatedIndexers) as $indexerId) {
            try {
                $indexer = $this->indexerRegistry->get($indexerId);
                if (!$indexer->isScheduled()) {
                    $indexer->invalidate();
                }
            } catch (\InvalidArgumentException $e) {
            }
        }

        return $this;
    }

    /**
     * Gets array of entities and appropriate behaviours
     * array(
     *     <entity_code> => array(
     *         'token' => <behavior_class_name>,
     *         'code'  => <behavior_model_code>,
     *     ),
     *     ...
     * )
     *
     * @return array
     * @throws LocalizedException
     */
    public function getEntityBehaviors()
    {
        $behaviourData = [];
        $entities = $this->_importConfig->getEntities();
        foreach ($entities as $entityCode => $entityData) {
            $behaviorClassName = isset($entityData['behaviorModel']) ? $entityData['behaviorModel'] : null;
            if ($behaviorClassName && class_exists($behaviorClassName)) {
                /** @var $behavior AbstractBehavior */
                $behavior = $this->_behaviorFactory->create($behaviorClassName);
                $behaviourData[$entityCode] = [
                    'token' => $behaviorClassName,
                    'code' => $behavior->getCode() . '_behavior',
                    'notes' => $behavior->getNotes($entityCode),
                ];
            } else {
                throw new LocalizedException(
                    __('The behavior token for %1 is invalid.', $entityCode)
                );
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
        $uniqueBehaviors = [];
        $behaviourData = $this->getEntityBehaviors();
        foreach ($behaviourData as $behavior) {
            $behaviorCode = $behavior['code'];
            if (!isset($uniqueBehaviors[$behaviorCode])) {
                $uniqueBehaviors[$behaviorCode] = $behavior['token'];
            }
        }
        return $uniqueBehaviors;
    }

    /**
     * Retrieve processed reports entity types
     *
     * @param string|null $entity
     * @return bool
     * @throws LocalizedException
     */
    public function isReportEntityType($entity = null)
    {
        $result = false;
        if (!$entity) {
            $entity = $this->getEntity();
        }
        if ($entity !== null && $this->_getEntityAdapter()->getEntityTypeCode() != $entity) {
            $entities = $this->_importConfig->getEntities();
            if (isset($entities[$entity])) {
                try {
                    $result = $this->_getEntityAdapter()->isNeedToLogInHistory();
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __('Please enter a correct entity model')
                    );
                }
            } else {
                throw new LocalizedException(__('Please enter a correct entity model'));
            }
        } else {
            $result = $this->_getEntityAdapter()->isNeedToLogInHistory();
        }
        return $result;
    }

    /**
     * Create history report.
     *
     * @param string $sourceFileRelative
     * @param string $entity
     * @param string $extension
     * @param array $result
     * @return $this
     * @throws LocalizedException
     */
    protected function createHistoryReport($sourceFileRelative, $entity, $extension = null, $result = null)
    {
        if ($this->isReportEntityType($entity)) {
            if (is_array($sourceFileRelative)) {
                $fileName = $sourceFileRelative['file_name'];
                $sourceFileRelative = $this->_varDirectory->getRelativePath(self::IMPORT_DIR . $fileName);
            } elseif (isset($result['name'])) {
                $fileName = $result['name'];
            } elseif ($extension !== null) {
                $fileName = $entity . $extension;
            } else {
                $fileName = basename($sourceFileRelative);
            }
            $copyName = $this->localeDate->gmtTimestamp() . '_' . $fileName;
            $copyFile = self::IMPORT_HISTORY_DIR . $copyName;
            try {
                if ($this->_varDirectory->isExist($sourceFileRelative)) {
                    $this->_varDirectory->copyFile($sourceFileRelative, $copyFile);
                } else {
                    $content = $this->_varDirectory->getDriver()->fileGetContents($sourceFileRelative);
                    $this->_varDirectory->writeFile($copyFile, $content);
                }
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('Source file coping failed'));
            }
            $this->importHistoryModel->addReport($copyName);
        }
        return $this;
    }

    /**
     * Get count of created items
     *
     * @return int
     */
    public function getCreatedItemsCount()
    {
        return $this->_getEntityAdapter()->getCreatedItemsCount();
    }

    /**
     * Get count of updated items
     *
     * @return int
     */
    public function getUpdatedItemsCount()
    {
        return $this->_getEntityAdapter()->getUpdatedItemsCount();
    }

    /**
     * Get count of deleted items
     *
     * @return int
     */
    public function getDeletedItemsCount()
    {
        return $this->_getEntityAdapter()->getDeletedItemsCount();
    }
}
