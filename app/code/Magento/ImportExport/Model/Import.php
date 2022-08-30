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
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\ImportExport\Model\Source\Upload;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Import model
 *
 * @api
 *
 * @method string getBehavior() getBehavior()
 * @method self setEntity() setEntity(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Import extends AbstractModel
{
    public const BEHAVIOR_APPEND = 'append';
    public const BEHAVIOR_ADD_UPDATE = 'add_update';
    public const BEHAVIOR_REPLACE = 'replace';
    public const BEHAVIOR_DELETE = 'delete';
    public const BEHAVIOR_CUSTOM = 'custom';

    /**
     * Import source file.
     */
    public const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * Import image archive.
     */
    public const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';

    /**
     * Import images file directory.
     */
    public const FIELD_NAME_IMG_FILE_DIR = 'import_images_file_dir';

    /**
     * Allowed errors count field name
     */
    public const FIELD_NAME_ALLOWED_ERROR_COUNT = 'allowed_error_count';

    /**
     * Validation startegt field name
     */
    public const FIELD_NAME_VALIDATION_STRATEGY = 'validation_strategy';

    /**
     * Import field separator.
     */
    public const FIELD_FIELD_SEPARATOR = '_import_field_separator';

    /**
     * Import multiple value separator.
     */
    public const FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR = '_import_multiple_value_separator';

    /**
     * Import empty attribute value constant.
     */
    public const FIELD_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '_import_empty_attribute_value_constant';

    /**
     * Allow multiple values wrapping in double quotes for additional attributes.
     */
    public const FIELDS_ENCLOSURE = 'fields_enclosure';

    /**
     * default delimiter for several values in one cell as default for FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR
     */
    public const DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR = ',';

    /**
     * Import empty attribute default value
     */
    public const DEFAULT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = '__EMPTY__VALUE__';
    public const DEFAULT_SIZE = 50;
    public const MAX_IMPORT_CHUNKS = 4;
    public const IMPORT_HISTORY_DIR = 'import_history/';
    public const IMPORT_DIR = 'import/';

    /**
     * @var AbstractEntity|ImportAbstractEntity
     */
    protected $_entityAdapter;

    /**
     * @Deprecated Property isn't used
     * @var DataHelper
     */
    protected $_importExportData = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_coreConfig;

    /**
     * @var ConfigInterface
     */
    protected $_importConfig;

    /**
     * @var Factory
     */
    protected $_entityFactory;

    /**
     * @var Data
     */
    protected $_importData;

    /**
     * @var CsvFactory
     */
    protected $_csvFactory;

    /**
     * @Deprecated Property isn't used
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
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @Deprecated Property isn't used
     * @var Random
     */
    private $random;

    /**
     * @var Upload
     */
    private $upload;

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DataHelper $importExportData
     * @param ScopeConfigInterface $coreConfig
     * @param Import\ConfigInterface $importConfig
     * @param Import\Entity\Factory $entityFactory
     * @param Data $importData
     * @param Export\Adapter\CsvFactory $csvFactory
     * @param FileTransferFactory $httpFactory
     * @param UploaderFactory $uploaderFactory
     * @param Source\Import\Behavior\Factory $behaviorFactory
     * @param IndexerRegistry $indexerRegistry
     * @param History $importHistoryModel
     * @param DateTime $localeDate
     * @param array $data
     * @param ManagerInterface|null $messageManager
     * @param Random|null $random
     * @param Upload|null $upload
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        Data $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        array $data = [],
        ManagerInterface $messageManager = null,
        Random $random = null,
        Upload $upload = null
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
        $this->messageManager = $messageManager ?: ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->random = $random ?: ObjectManager::getInstance()
            ->get(Random::class);
        $this->upload = $upload ?: ObjectManager::getInstance()
            ->get(Upload::class);
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
     * @Deprecated
     * @see \Magento\ImportExport\Model\Import\Source\Factory::create()
     * @param string $sourceFile Full path to source file
     * @return AbstractSource
     * @throws FileSystemException
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
     * @throws LocalizedException
     */
    public function getOperationResultMessages(ProcessingErrorAggregatorInterface $validationResult)
    {
        $messages = [];
        if ($this->getProcessedRowsCount()) {
            if ($validationResult->isErrorLimitExceeded()) {
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
     * phpcs:disable Magento2.Functions.StaticFunction
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
     * @return Data
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
     * @throws LocalizedException
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_getEntityAdapter()->getProcessedEntitiesCount();
    }

    /**
     * Returns number of checked rows.
     *
     * @return int
     * @throws LocalizedException
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
        //Validating images temporary directory path if the constraint has been provided
        if ($this->hasData('images_base_directory')
            && $this->getData('images_base_directory') instanceof Filesystem\Directory\ReadInterface
        ) {
            /** @var Filesystem\Directory\ReadInterface $imagesDirectory */
            $imagesDirectory = $this->getData('images_base_directory');
            if (!$imagesDirectory->isReadable()) {
                $rootWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT);
                $rootWrite->create($imagesDirectory->getAbsolutePath());
            }
            try {
                $this->setData(
                    self::FIELD_NAME_IMG_FILE_DIR,
                    $imagesDirectory->getAbsolutePath($this->getData(self::FIELD_NAME_IMG_FILE_DIR))
                );
                $this->_getEntityAdapter()->setParameters($this->getData());
            } catch (ValidatorException $exception) {
                throw new LocalizedException(__('Images file directory is outside required directory'), $exception);
            }
        }

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
     * Process import.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function processImport()
    {
        return $this->_getEntityAdapter()->importData();
    }

    /**
     * Import possibility getter.
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isImportAllowed()
    {
        return $this->_getEntityAdapter()->isImportAllowed();
    }

    /**
     * Get error aggregator instance.
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws LocalizedException
     */
    public function getErrorAggregator()
    {
        return $this->_getEntityAdapter()->getErrorAggregator();
    }

    /**
     * Move uploaded file.
     *
     * @throws LocalizedException
     * @return string Source file path
     */
    public function uploadSource()
    {
        $entity = $this->getEntity();
        $result = $this->upload->uploadSource($entity);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
        $sourceFile = $this->getWorkingDir() . $entity . '.' . $extension;
        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);
        $this->_removeBom($sourceFile);
        $this->createHistoryReport($sourceFileRelative, $entity, $extension, $result);
        return $sourceFile;
    }

    /**
     * Move uploaded file and provide source instance.
     *
     * @return Import\AbstractSource
     * @throws LocalizedException
     * @since 100.2.7
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
     * @throws FileSystemException
     */
    protected function _removeBom($sourceFile)
    {
        $driver = $this->_varDirectory->getDriver();
        $string = $driver->fileGetContents($this->_varDirectory->getAbsolutePath($sourceFile));
        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            $driver->filePutContents($this->_varDirectory->getAbsolutePath($sourceFile), $string);
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
     * @throws LocalizedException
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
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->addLogComment($messages);

        $result = !$errorAggregator->isErrorLimitExceeded();
        if ($result) {
            $this->addLogComment(__('Import data validation is complete.'));
        }
        return $result;
    }

    /**
     * Invalidate indexes by process codes.
     *
     * @return $this
     * @throws LocalizedException
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
                // phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
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
     * @throws LocalizedException
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
     * Create history report
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
                // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
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
     * @throws LocalizedException
     */
    public function getCreatedItemsCount()
    {
        return $this->_getEntityAdapter()->getCreatedItemsCount();
    }

    /**
     * Get count of updated items
     *
     * @return int
     * @throws LocalizedException
     */
    public function getUpdatedItemsCount()
    {
        return $this->_getEntityAdapter()->getUpdatedItemsCount();
    }

    /**
     * Get count of deleted items
     *
     * @return int
     * @throws LocalizedException
     */
    public function getDeletedItemsCount()
    {
        return $this->_getEntityAdapter()->getDeletedItemsCount();
    }
}
