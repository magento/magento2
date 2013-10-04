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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Eav Indexer Model
 *
 * @method \Magento\Catalog\Model\Resource\Product\Indexer\Eav _getResource()
 * @method \Magento\Catalog\Model\Resource\Product\Indexer\Eav getResource()
 * @method \Magento\Catalog\Model\Product\Indexer\Eav setEntityId(int $value)
 * @method int getAttributeId()
 * @method \Magento\Catalog\Model\Product\Indexer\Eav setAttributeId(int $value)
 * @method int getStoreId()
 * @method \Magento\Catalog\Model\Product\Indexer\Eav setStoreId(int $value)
 * @method int getValue()
 * @method \Magento\Catalog\Model\Product\Indexer\Eav setValue(int $value)
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Indexer;

class Eav extends \Magento\Index\Model\Indexer\AbstractIndexer
{
    /**
     * @var array
     */
    protected $_matchedEntities = array(
        \Magento\Catalog\Model\Product::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_DELETE,
            \Magento\Index\Model\Event::TYPE_MASS_ACTION,
        ),
        \Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
        ),
    );

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Product Attributes');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Index product attributes for layered navigation building');
    }

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Indexer\Eav');
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerEvent(\Magento\Index\Model\Event $event)
    {
        $entity = $event->getEntity();

        if ($entity == \Magento\Catalog\Model\Product::ENTITY) {
            switch ($event->getType()) {
                case \Magento\Index\Model\Event::TYPE_DELETE:
                    $this->_registerCatalogProductDeleteEvent($event);
                    break;

                case \Magento\Index\Model\Event::TYPE_SAVE:
                    $this->_registerCatalogProductSaveEvent($event);
                    break;

                case \Magento\Index\Model\Event::TYPE_MASS_ACTION:
                    $this->_registerCatalogProductMassActionEvent($event);
                    break;
            }
        } else if ($entity == \Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY) {
            switch ($event->getType()) {
                case \Magento\Index\Model\Event::TYPE_SAVE:
                    $this->_registerCatalogAttributeSaveEvent($event);
                    break;
            }
        }
    }

    /**
     * Check is attribute indexable in EAV
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute|string $attribute
     * @return bool
     */
    protected function _attributeIsIndexable($attribute)
    {
        if (!$attribute instanceof \Magento\Catalog\Model\Resource\Eav\Attribute) {
            $attribute = $this->_eavConfig
                ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attribute);
        }

        return $attribute->isIndexable();
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Eav
     */
    protected function _registerCatalogProductSaveEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product    = $event->getDataObject();
        $attributes = $product->getAttributes();
        $reindexEav = $product->getForceReindexRequired();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($this->_attributeIsIndexable($attribute) && $product->dataHasChangedFor($attributeCode)) {
                $reindexEav = true;
                break;
            }
        }

        if ($reindexEav) {
            $event->addNewData('reindex_eav', $reindexEav);
        }

        return $this;
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Eav
     */
    protected function _registerCatalogProductDeleteEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product    = $event->getDataObject();

        $parentIds  = $this->_getResource()->getRelationsByChild($product->getId());
        if ($parentIds) {
            $event->addNewData('reindex_eav_parent_ids', $parentIds);
        }

        return $this;
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Eav
     */
    protected function _registerCatalogProductMassActionEvent(\Magento\Index\Model\Event $event)
    {
        $reindexEav = false;

        /* @var $actionObject \Magento\Object */
        $actionObject = $event->getDataObject();
        // check if attributes changed
        $attrData = $actionObject->getAttributesData();
        if (is_array($attrData)) {
            foreach (array_keys($attrData) as $attributeCode) {
                if ($this->_attributeIsIndexable($attributeCode)) {
                    $reindexEav = true;
                    break;
                }
            }
        }

        // check changed websites
        if ($actionObject->getWebsiteIds()) {
            $reindexEav = true;
        }

        // register affected products
        if ($reindexEav) {
            $event->addNewData('reindex_eav_product_ids', $actionObject->getProductIds());
        }

        return $this;
    }

    /**
     * Register data required by process attribute save in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Eav
     */
    protected function _registerCatalogAttributeSaveEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
        $attribute = $event->getDataObject();
        if ($attribute->isIndexable()) {
            $before = $attribute->getOrigData('is_filterable')
                || $attribute->getOrigData('is_filterable_in_search')
                || $attribute->getOrigData('is_visible_in_advanced_search');
            $after  = $attribute->getData('is_filterable')
                || $attribute->getData('is_filterable_in_search')
                || $attribute->getData('is_visible_in_advanced_search');

            if (!$before && $after || $before && !$after) {
                $event->addNewData('reindex_attribute', 1);
                $event->addNewData('attribute_index_type', $attribute->getIndexType());
                $event->addNewData('is_indexable', $after);
            }
        }

        return $this;
    }

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if (!empty($data['catalog_product_eav_reindex_all'])) {
            $this->reindexAll();
        }
        if (empty($data['catalog_product_eav_skip_call_event_handler'])) {
            $this->callEventHandler($event);
        }
    }
}
