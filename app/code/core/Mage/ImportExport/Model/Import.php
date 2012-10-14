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
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method string getBehavior() getBehavior()
 * @method Mage_ImportExport_Model_Import setEntity() setEntity(string $value)
 */
class Mage_ImportExport_Model_Import extends Mage_ImportExport_Model_Abstract
{
    /**
     * Import entities config key
     */
    const CONFIG_KEY_ENTITIES = 'global/importexport/import_entities';

    /**#@+
     * Import behaviors
     */
    const BEHAVIOR_APPEND     = 'append';
    const BEHAVIOR_ADD_UPDATE = 'add_update';
    const BEHAVIOR_REPLACE    = 'replace';
    const BEHAVIOR_DELETE     = 'delete';
    const BEHAVIOR_CUSTOM     = 'custom';
    /**#@-*/

    /**#@+
     * Form field names (and IDs)
     */
    const FIELD_NAME_SOURCE_FILE      = 'import_file';
    const FIELD_NAME_IMG_ARCHIVE_FILE = 'import_image_archive';
    /**#@-*/

    /**#@+
     * Import constants
     *
     */
    const DEFAULT_SIZE      = 50;
    const MAX_IMPORT_CHUNKS = 4;
    /**#@-*/

    /**
     * Entity adapter.
     *
     * @var Mage_ImportExport_Model_Import_Entity_Abstract
     */
    protected $_entityAdapter;

    /**
     * Entity invalidated indexes.
     *
     * @var Mage_ImportExport_Model_Import_Entity_Abstract
     */
     protected static $_entityInvalidatedIndexes = array (
        'catalog_product' => array (
            'catalog_product_price',
            'catalog_category_product',
            'catalogsearch_fulltext',
            'catalog_product_flat',
        )
    );

    /**
     * Create instance of entity adapter and return it
     *
     * @throws Mage_Core_Exception
     * @return Mage_ImportExport_Model_Import_Entity_Abstract|Mage_ImportExport_Model_Import_EntityAbstract
     */
    protected function _getEntityAdapter()
    {
        if (!$this->_entityAdapter) {
            $entityTypes = Mage_ImportExport_Model_Config::getModels(self::CONFIG_KEY_ENTITIES);

            if (isset($entityTypes[$this->getEntity()])) {
                try {
                    $this->_entityAdapter = Mage::getModel($entityTypes[$this->getEntity()]['model']);
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::throwException(
                        Mage::helper('Mage_ImportExport_Helper_Data')->__('Invalid entity model')
                    );
                }
                if (!($this->_entityAdapter instanceof Mage_ImportExport_Model_Import_Entity_Abstract)
                    && !($this->_entityAdapter instanceof Mage_ImportExport_Model_Import_EntityAbstract)
                ) {
                    Mage::throwException(
                        Mage::helper('Mage_ImportExport_Helper_Data')
                            ->__('Entity adapter object must be an instance of %s or %s',
                                'Mage_ImportExport_Model_Import_Entity_Abstract',
                                'Mage_ImportExport_Model_Import_EntityAbstract'
                            )
                    );
                }

                // check for entity codes integrity
                if ($this->getEntity() != $this->_entityAdapter->getEntityTypeCode()) {
                    Mage::throwException(
                        Mage::helper('Mage_ImportExport_Helper_Data')
                            ->__('Input entity code is not equal to entity adapter code')
                    );
                }
            } else {
                Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Invalid entity'));
            }
            $this->_entityAdapter->setParameters($this->getData());
        }
        return $this->_entityAdapter;
    }

    /**
     * Returns source adapter object.
     *
     * @param string $sourceFile Full path to source file
     * @return Mage_ImportExport_Model_Import_Adapter_Abstract
     */
    protected function _getSourceAdapter($sourceFile)
    {
        return Mage_ImportExport_Model_Import_Adapter::findAdapterFor($sourceFile);
    }

    /**
     * Return operation result messages
     *
     * @param bool $validationResult
     * @return array
     */
    public function getOperationResultMessages($validationResult)
    {
        $messages = array();
        if ($this->getProcessedRowsCount()) {
            if (!$validationResult) {
                if ($this->getProcessedRowsCount() == $this->getInvalidRowsCount()) {
                    $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                        ->__('File is totally invalid. Please fix errors and re-upload file');
                } elseif ($this->getErrorsCount() >= $this->getErrorsLimit()) {
                    $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                        ->__('Errors limit (%d) reached. Please fix errors and re-upload file',
                            $this->getErrorsLimit()
                        );
                } else {
                    if ($this->isImportAllowed()) {
                        $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                            ->__('Please fix errors and re-upload file');
                    } else {
                        $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                            ->__('File is partially valid, but import is not possible');
                    }
                }
                // errors info
                foreach ($this->getErrors() as $errorCode => $rows) {
                    $error = $errorCode . ' '
                        . Mage::helper('Mage_ImportExport_Helper_Data')->__('in rows') . ': '
                        . implode(', ', $rows);
                    $messages[] = $error;
                }
            } else {
                if ($this->isImportAllowed()) {
                    $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                        ->__('Validation finished successfully');
                } else {
                    $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                        ->__('File is valid, but import is not possible');
                }
            }
            $notices = $this->getNotices();
            if (is_array($notices)) {
                $messages = array_merge($messages, $notices);
            }
            $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')
                ->__('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d',
                    $this->getProcessedRowsCount(), $this->getProcessedEntitiesCount(),
                    $this->getInvalidRowsCount(), $this->getErrorsCount()
                );
        } else {
            $messages[] = Mage::helper('Mage_ImportExport_Helper_Data')->__('File does not contain data.');
        }
        return $messages;
    }

    /**
     * Get attribute type for upcoming validation.
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|Mage_Eav_Model_Entity_Attribute $attribute
     * @return string
     */
    public static function getAttributeType(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($attribute->usesSource()) {
            return $attribute->getFrontendInput() == 'multiselect' ?
                'multiselect' : 'select';
        } elseif ($attribute->isStatic()) {
            return $attribute->getFrontendInput() == 'date' ? 'datetime' : 'varchar';
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * DB data source model getter.
     *
     * @static
     * @return Mage_ImportExport_Model_Resource_Import_Data
     */
    public static function getDataSourceModel()
    {
        return Mage::getResourceSingleton('Mage_ImportExport_Model_Resource_Import_Data');
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
     * @throw Mage_Core_Exception
     * @return string
     */
    public function getEntity()
    {
        if (empty($this->_data['entity'])) {
            Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Entity is unknown'));
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
     * @return array
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
    public static function getWorkingDir()
    {
        return Mage::getBaseDir('var') . DS . 'importexport' . DS;
    }

    /**
     * Import source file structure to DB.
     *
     * @return bool
     */
    public function importSource()
    {
        $this->setData(array(
            'entity'         => self::getDataSourceModel()->getEntityTypeCode(),
            'behavior'       => self::getDataSourceModel()->getBehavior(),
        ));

        $this->addLogComment(
            Mage::helper('Mage_ImportExport_Helper_Data')
                ->__('Begin import of "%s" with "%s" behavior',
                    $this->getEntity(),
                    $this->getBehavior()
                )
        );

        $result = $this->_getEntityAdapter()->importData();

        $this->addLogComment(array(
            Mage::helper('Mage_ImportExport_Helper_Data')
                ->__('Checked rows: %d, checked entities: %d, invalid rows: %d, total errors: %d',
                    $this->getProcessedRowsCount(),
                    $this->getProcessedEntitiesCount(),
                    $this->getInvalidRowsCount(),
                    $this->getErrorsCount()
                ),
            Mage::helper('Mage_ImportExport_Helper_Data')->__('Import has been done successfuly.')
        ));

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
        /** @var $writer Mage_ImportExport_Model_Export_Adapter_Csv */
        $writer  = Mage::getModel('Mage_ImportExport_Model_Export_Adapter_Csv', self::getWorkingDir() . "big0.csv");
        $regExps = array('last' => '/(.*?)(\d+)$/', 'middle' => '/(.*?)(\d+)(.*)$/');
        $colReg  = array(
            'sku' => 'last', 'name' => 'last', 'description' => 'last', 'short_description' => 'last',
            'url_key' => 'middle', 'meta_title' => 'last', 'meta_keyword' => 'last', 'meta_description' => 'last',
            '_links_related_sku' => 'last', '_links_crosssell_sku' => 'last', '_links_upsell_sku' => 'last',
            '_custom_option_sku' => 'middle', '_custom_option_row_sku' => 'middle', '_super_products_sku' => 'last',
            '_associated_sku' => 'last'
        );
        $size = self::DEFAULT_SIZE;

        $filename = 'catalog_product.csv';
        $filenameFormat = 'big%s.csv';
        foreach ($this->_getSourceAdapter(self::getWorkingDir() . $filename) as $row) {
            $writer->writeRow($row);
        }
        $count = self::MAX_IMPORT_CHUNKS;
        for ($i = 1; $i < $count; $i++) {
            $writer = Mage::getModel(
                'Mage_ImportExport_Model_Export_Adapter_Csv',
                self::getWorkingDir() . sprintf($filenameFormat, $i)
            );

            $adapter = $this->_getSourceAdapter(self::getWorkingDir() . sprintf($filenameFormat, $i - 1));
            foreach ($adapter as $row) {
                $writer->writeRow($row);
            }
            $adapter = $this->_getSourceAdapter(self::getWorkingDir() . sprintf($filenameFormat, $i - 1));
            foreach ($adapter as $row) {
                foreach ($colReg as $colName => $regExpType) {
                    if (!empty($row[$colName])) {
                        preg_match($regExps[$regExpType], $row[$colName], $m);

                        $row[$colName] = $m[1] . ($m[2] + $size) . ('middle' == $regExpType ? $m[3] : '');
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
     * @throws Mage_Core_Exception
     * @return string Source file path
     */
    public function uploadSource()
    {
        $entity    = $this->getEntity();
        /** @var $uploader Mage_Core_Model_File_Uploader */
        $uploader  = Mage::getModel('Mage_Core_Model_File_Uploader', self::FIELD_NAME_SOURCE_FILE);
        $uploader->skipDbProcessing(true);
        $result    = $uploader->save(self::getWorkingDir());
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);

        $uploadedFile = $result['path'] . $result['file'];
        if (!$extension) {
            unlink($uploadedFile);
            Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Uploaded file has no extension'));
        }
        $sourceFile = self::getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if (file_exists($sourceFile)) {
                unlink($sourceFile);
            }

            if (!@rename($uploadedFile, $sourceFile)) {
                Mage::throwException(Mage::helper('Mage_ImportExport_Helper_Data')->__('Source file moving failed'));
            }
        }
        // trying to create source adapter for file and catch possible exception to be convinced in its adequacy
        try {
            $this->_getSourceAdapter($sourceFile);
        } catch (Exception $e) {
            unlink($sourceFile);
            Mage::throwException($e->getMessage());
        }
        return $sourceFile;
    }

    /**
     * Validates source file and returns validation result.
     *
     * @param Mage_ImportExport_Model_Import_Adapter_Abstract $source
     * @return bool
     */
    public function validateSource(Mage_ImportExport_Model_Import_Adapter_Abstract $source)
    {
        $this->addLogComment(Mage::helper('Mage_ImportExport_Helper_Data')->__('Begin data validation'));
        $adapter = $this->_getEntityAdapter()->setSource($source);
        $result = $adapter->isDataValid();

        $messages = $this->getOperationResultMessages($result);
        $this->addLogComment($messages);
        if ($result) {
            $this->addLogComment(Mage::helper('Mage_ImportExport_Helper_Data')->__('Done import data validation'));
        }
        return $result;
    }

    /**
     * Invalidate indexes by process codes.
     *
     * @return Mage_ImportExport_Model_Import
     */
    public function invalidateIndex()
    {
        if (!isset(self::$_entityInvalidatedIndexes[$this->getEntity()])) {
            return $this;
        }

        $indexers = self::$_entityInvalidatedIndexes[$this->getEntity()];
        foreach ($indexers as $indexer) {
            $indexProcess = Mage::getSingleton('Mage_Index_Model_Indexer')->getProcessByCode($indexer);
            if ($indexProcess) {
                $indexProcess->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
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
     * @static
     * @return array
     */
    public static function getEntityBehaviors()
    {
        $behaviourData = array();
        $entitiesConfig = Mage::getConfig()->getNode(self::CONFIG_KEY_ENTITIES)
            ->asArray();
        foreach ($entitiesConfig as $entityCode => $entityData) {
            $behaviorToken = isset($entityData['behavior_token']) ? $entityData['behavior_token'] : null;
            if ($behaviorToken && Magento_Autoload::getInstance()->classExists($behaviorToken)) {
                /** @var $behaviorModel Mage_ImportExport_Model_Source_Import_BehaviorAbstract */
                $behaviorModel = Mage::getModel($behaviorToken);
                $behaviourData[$entityCode] = array(
                    'token' => $behaviorToken,
                    'code'  => $behaviorModel->getCode() . '_behavior',
                );
            } else {
                Mage::throwException(
                    Mage::helper('Mage_ImportExport_Helper_Data')->__('Invalid behavior token for %s', $entityCode)
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
     * @static
     * @return array
     */
    public static function getUniqueEntityBehaviors()
    {
        $uniqueBehaviors = array();
        $behaviourData = self::getEntityBehaviors();
        foreach ($behaviourData as $behavior) {
            $behaviorCode = $behavior['code'];
            if (!isset($uniqueBehaviors[$behaviorCode])) {
                $uniqueBehaviors[$behaviorCode] = $behavior['token'];
            }
        }
        return $uniqueBehaviors;
    }
}
