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
namespace Magento\Catalog\Model\Product\Indexer;

/**
 * @method \Magento\Catalog\Model\Resource\Product\Indexer\Price _getResource()
 * @method \Magento\Catalog\Model\Resource\Product\Indexer\Price getResource()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setEntityId(int $value)
 * @method int getCustomerGroupId()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setCustomerGroupId(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setWebsiteId(int $value)
 * @method int getTaxClassId()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setTaxClassId(int $value)
 * @method float getPrice()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setPrice(float $value)
 * @method float getFinalPrice()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setFinalPrice(float $value)
 * @method float getMinPrice()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setMinPrice(float $value)
 * @method float getMaxPrice()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setMaxPrice(float $value)
 * @method float getTierPrice()
 * @method \Magento\Catalog\Model\Product\Indexer\Price setTierPrice(float $value)
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Price extends \Magento\Index\Model\Indexer\AbstractIndexer
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_product_price_match_result';

    /**
     * Reindex price event type
     */
    const EVENT_TYPE_REINDEX_PRICE = 'catalog_reindex_price';

    /**
     * Matched Entities instruction array
     *
     * @var array
     */
    protected $_matchedEntities = array(
        \Magento\Catalog\Model\Product::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_DELETE,
            \Magento\Index\Model\Event::TYPE_MASS_ACTION,
            self::EVENT_TYPE_REINDEX_PRICE,
        ),
        \Magento\App\Config\ValueInterface::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
        \Magento\Customer\Model\Group::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        )
    );

    /**
     * @var string[]
     */
    protected $_relatedConfigSettings = array(
        \Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE,
        \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK
    );

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Indexer\Price');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Product Prices');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Index product prices');
    }

    /**
     * Retrieve attribute list has an effect on product price
     *
     * @return string[]
     */
    protected function _getDependentAttributes()
    {
        return array(
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'tax_class_id',
            'status',
            'required_options',
            'force_reindex_required'
        );
    }

    /**
     * Check if event can be matched by process.
     * Rewrited for checking configuration settings save (like price scope).
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    public function matchEvent(\Magento\Index\Model\Event $event)
    {
        $data       = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        if ($event->getEntity() == \Magento\App\Config\ValueInterface::ENTITY) {
            $data = $event->getDataObject();
            if ($data && in_array($data->getPath(), $this->_relatedConfigSettings)) {
                $result = $data->isValueChanged();
            } else {
                $result = false;
            }
        } elseif ($event->getEntity() == \Magento\Customer\Model\Group::ENTITY) {
            $result = $event->getDataObject() && $event->getDataObject()->isObjectNew();
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by catalog product delete process
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _registerCatalogProductDeleteEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $event->getDataObject();

        $parentIds = $this->_getResource()->getProductParentsByChild($product->getId());
        if ($parentIds) {
            $event->addNewData('reindex_price_parent_ids', $parentIds);
        }
    }

    /**
     * Register data required by catalog product save process
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _registerCatalogProductSaveEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product      = $event->getDataObject();
        $attributes   = $this->_getDependentAttributes();
        $reindexPrice = $product->getIsRelationsChanged() || $product->getIsCustomOptionChanged()
            || $product->dataHasChangedFor('tier_price_changed')
            || $product->getIsChangedWebsites()
            || $product->getForceReindexRequired();

        foreach ($attributes as $attributeCode) {
            $reindexPrice = $reindexPrice || $product->dataHasChangedFor($attributeCode);
        }

        if ($reindexPrice) {
            $event->addNewData('product_type_id', $product->getTypeId());
            $event->addNewData('reindex_price', 1);
        }
    }

    /**
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _registerCatalogProductMassActionEvent(\Magento\Index\Model\Event $event)
    {
        /* @var $actionObject \Magento\Object */
        $actionObject = $event->getDataObject();
        $attributes   = $this->_getDependentAttributes();
        $reindexPrice = false;

        // check if attributes changed
        $attrData = $actionObject->getAttributesData();
        if (is_array($attrData)) {
            foreach ($attributes as $attributeCode) {
                if (array_key_exists($attributeCode, $attrData)) {
                    $reindexPrice = true;
                    break;
                }
            }
        }

        // check changed websites
        if ($actionObject->getWebsiteIds()) {
            $reindexPrice = true;
        }

        // register affected products
        if ($reindexPrice) {
            $event->addNewData('reindex_price_product_ids', $actionObject->getProductIds());
        }
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _registerEvent(\Magento\Index\Model\Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();

        if ($entity == \Magento\App\Config\ValueInterface::ENTITY || $entity == \Magento\Customer\Model\Group::ENTITY) {
            $process = $event->getProcess();
            $process->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
        } else if ($entity == \Magento\Catalog\Model\Product::ENTITY) {
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
                case self::EVENT_TYPE_REINDEX_PRICE:
                    $event->addNewData('id', $event->getDataObject()->getId());
                    break;
                default:
                    break;
            }

            // call product type indexers registerEvent
            $indexers = $this->_getResource()->getTypeIndexers();
            foreach ($indexers as $indexer) {
                $indexer->registerEvent($event);
            }
        }
    }

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     * @return void
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if ($event->getType() == self::EVENT_TYPE_REINDEX_PRICE) {
            $this->_getResource()->reindexProductIds($data['id']);
            return;
        }
        if (!empty($data['catalog_product_price_reindex_all'])) {
            $this->reindexAll();
        }
        if (empty($data['catalog_product_price_skip_call_event_handler'])) {
            $this->callEventHandler($event);
        }
    }
}
