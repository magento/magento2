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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Eav;

use \Magento\Catalog\Model\Attribute\LockValidatorInterface;
/**
 * Catalog attribute model
 *
 * @method \Magento\Catalog\Model\Resource\Attribute _getResource()
 * @method \Magento\Catalog\Model\Resource\Attribute getResource()
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getFrontendInputRenderer()
 * @method string setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsVisible()
 * @method int setIsVisible(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsSearchable()
 * @method int setIsSearchable(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getSearchWeight()
 * @method int setSearchWeight(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsFilterable()
 * @method int setIsFilterable(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsComparable()
 * @method int setIsComparable(int $value)
 * @method int setIsVisibleOnFront(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsHtmlAllowedOnFront()
 * @method int setIsHtmlAllowedOnFront(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsUsedForPriceRules()
 * @method int setIsUsedForPriceRules(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsFilterableInSearch()
 * @method int setIsFilterableInSearch(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getUsedInProductListing()
 * @method int setUsedInProductListing(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getUsedForSortBy()
 * @method int setUsedForSortBy(int $value)
 * @method string setApplyTo(string $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsVisibleInAdvancedSearch()
 * @method int setIsVisibleInAdvancedSearch(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getPosition()
 * @method int setPosition(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsWysiwygEnabled()
 * @method int setIsWysiwygEnabled(int $value)
 * @method \Magento\Catalog\Model\Resource\Eav\Attribute getIsUsedForPromoRules()
 * @method int setIsUsedForPromoRules(int $value)
 * @method string getFrontendLabel()
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    const SCOPE_STORE                           = 0;
    const SCOPE_GLOBAL                          = 1;
    const SCOPE_WEBSITE                         = 2;

    const MODULE_NAME                           = 'Magento_Catalog';
    const ENTITY                                = 'catalog_eav_attribute';

    /**
     * Index indexer
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexIndexer;

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject                     = 'attribute';

    /**
     * Array with labels
     *
     * @var array
     */
    static protected $_labels                   = null;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                     = 'catalog_entity_attribute';

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_productFlatIndexerHelper;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Catalog\Model\ProductFactory $catalogProductFactory
     * @param \Magento\Index\Model\Indexer $indexIndexer
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $productFlatIndexerHelper
     * @param LockValidatorInterface $lockValidator
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Index\Model\Indexer $indexIndexer,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Helper\Product\Flat\Indexer $productFlatIndexerHelper,
        LockValidatorInterface $lockValidator,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_indexIndexer = $indexIndexer;
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productFlatIndexerHelper = $productFlatIndexerHelper;
        $this->attrLockValidator = $lockValidator;
        parent::__construct(
            $context,
            $registry,
            $coreData,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $locale,
            $catalogProductFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Attribute');
    }

    /**
     * Processing object before save data
     *
     * @return \Magento\Core\Model\AbstractModel
     * @throws \Magento\Core\Exception
     */
    protected function _beforeSave()
    {
        $this->setData('modulePrefix', self::MODULE_NAME);
        if (isset($this->_origData['is_global'])) {
            if (!isset($this->_data['is_global'])) {
                $this->_data['is_global'] = self::SCOPE_GLOBAL;
            }
            if ($this->_data['is_global'] != $this->_origData['is_global']) {
                try {
                    $this->attrLockValidator->validate($this);
                } catch (\Magento\Core\Exception $exception) {
                    throw new \Magento\Core\Exception(
                        __('Do not change the scope. ' . $exception->getMessage())
                    );
                }
            }
        }
        if ($this->getFrontendInput() == 'price') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel('Magento\Catalog\Model\Product\Attribute\Backend\Price');
            }
        }
        if ($this->getFrontendInput() == 'textarea') {
            if ($this->getIsWysiwygEnabled()) {
                $this->setIsHtmlAllowedOnFront(1);
            }
        }
        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Core\Model\AbstractModel
     */
    protected function _afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        $this->_eavConfig->clear();

        $enableBefore = ($this->getOrigData('backend_type') == 'static')
            || ($this->_productFlatIndexerHelper->isAddFilterableAttributes() && $this->getOrigData('is_filterable') > 0)
            || ($this->getOrigData('used_in_product_listing') == 1)
            || ($this->getOrigData('used_for_sort_by') == 1);
        $enableAfter = ($this->getData('backend_type') == 'static')
            || ($this->_productFlatIndexerHelper->isAddFilterableAttributes() && $this->getData('is_filterable') > 0)
            || ($this->getData('used_in_product_listing') == 1)
            || ($this->getData('used_for_sort_by') == 1);

        if ($enableBefore != $enableAfter) {
            $this->_productFlatIndexerProcessor->markIndexerAsInvalid();
        }

        $this->_indexIndexer->processEntityAction(
            $this, self::ENTITY, \Magento\Index\Model\Event::TYPE_SAVE
        );
        return parent::_afterSave();
    }

    /**
     * Register indexing event before delete catalog eav attribute
     *
     * @return $this
     * @throws \Magento\Core\Exception
     */
    protected function _beforeDelete()
    {
        $this->attrLockValidator->validate($this);
        $this->_indexIndexer->logEvent(
            $this, self::ENTITY, \Magento\Index\Model\Event::TYPE_DELETE
        );
        return parent::_beforeDelete();
    }

    /**
     * Init indexing process after catalog eav attribute delete commit
     *
     * @return $this
     */
    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();

        $this->_productFlatIndexerProcessor->markIndexerAsInvalid();

        $this->_indexIndexer->indexEvents(
            self::ENTITY, \Magento\Index\Model\Event::TYPE_DELETE
        );
        return $this;
    }

    /**
     * Return is attribute global
     *
     * @return integer
     */
    public function getIsGlobal()
    {
        return $this->_getData('is_global');
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @return bool
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @return bool
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @return bool
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        $dataObject = $this->getDataObject();
        if ($dataObject) {
            return $dataObject->getStoreId();
        }
        return $this->getData('store_id');
    }

    /**
     * Retrieve apply to products array
     * Return empty array if applied to all products
     *
     * @return string[]
     */
    public function getApplyTo()
    {
        if ($this->getData('apply_to')) {
            if (is_array($this->getData('apply_to'))) {
                return $this->getData('apply_to');
            }
            return explode(',', $this->getData('apply_to'));
        } else {
            return array();
        }
    }

    /**
     * Retrieve source model
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function getSourceModel()
    {
        $model = $this->getData('source_model');
        if (empty($model)) {
            if ($this->getBackendType() == 'int' && $this->getFrontendInput() == 'select') {
                return $this->_getDefaultSourceModel();
            }
        }
        return $model;
    }

    /**
     * Whether allowed for rule condition
     *
     * @return bool
     */
    public function isAllowedForRuleCondition()
    {
        $allowedInputTypes = array(
            'boolean',
            'date',
            'datetime',
            'multiselect',
            'price',
            'select',
            'text',
            'textarea',
            'weight',
        );
        return $this->getIsVisible() && in_array($this->getFrontendInput(), $allowedInputTypes);
    }

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel()
    {
        return 'Magento\Eav\Model\Entity\Attribute\Source\Table';
    }

    /**
     * Check is an attribute used in EAV index
     *
     * @return bool
     */
    public function isIndexable()
    {
        // exclude price attribute
        if ($this->getAttributeCode() == 'price') {
            return false;
        }

        if (!$this->getIsFilterableInSearch() && !$this->getIsVisibleInAdvancedSearch() && !$this->getIsFilterable()) {
            return false;
        }

        $backendType    = $this->getBackendType();
        $frontendInput  = $this->getFrontendInput();

        if ($backendType == 'int' && $frontendInput == 'select') {
            return true;
        } else if ($backendType == 'varchar' && $frontendInput == 'multiselect') {
            return true;
        } else if ($backendType == 'decimal') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve index type for indexable attribute
     *
     * @return string|false
     */
    public function getIndexType()
    {
        if (!$this->isIndexable()) {
            return false;
        }
        if ($this->getBackendType() == 'decimal') {
            return 'decimal';
        }

        return 'source';
    }
}
