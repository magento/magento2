<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Bundle Selection Model
 *
 * @method int getSelectionId()
 * @method \Magento\Bundle\Model\Selection setSelectionId(int $value)
 * @method int getOptionId()
 * @method \Magento\Bundle\Model\Selection setOptionId(int $value)
 * @method int getParentProductId()
 * @method \Magento\Bundle\Model\Selection setParentProductId(int $value)
 * @method int getProductId()
 * @method \Magento\Bundle\Model\Selection setProductId(int $value)
 * @method int getPosition()
 * @method \Magento\Bundle\Model\Selection setPosition(int $value)
 * @method int getIsDefault()
 * @method \Magento\Bundle\Model\Selection setIsDefault(int $value)
 * @method int getWebsiteId()
 * @method \Magento\Bundle\Model\Selection setWebsiteId(int $value)
 * @method int getSelectionPriceType()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceType(int $value)
 * @method float getSelectionPriceValue()
 * @method \Magento\Bundle\Model\Selection setSelectionPriceValue(float $value)
 * @method float getSelectionQty()
 * @method \Magento\Bundle\Model\Selection setSelectionQty(float $value)
 * @method int getSelectionCanChangeQty()
 * @method \Magento\Bundle\Model\Selection setSelectionCanChangeQty(int $value)
 * @api
 * @since 100.0.2
 */
class Selection extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Bundle\Model\ResourceModel\Selection $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Bundle\Model\ResourceModel\Selection $resource,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Bundle\Model\ResourceModel\Selection::class);
        parent::_construct();
    }

    /**
     * Processing object before save data
     *
     * @return void
     */
    public function beforeSave()
    {
        if (!$this->_catalogData->isPriceGlobal() && $this->getWebsiteId()) {
            $this->setData('tmp_selection_price_value', $this->getSelectionPriceValue());
            $this->setData('tmp_selection_price_type', $this->getSelectionPriceType());
            $this->setSelectionPriceValue($this->getOrigData('selection_price_value'));
            $this->setSelectionPriceType($this->getOrigData('selection_price_type'));
        }
        parent::beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        if (!$this->_catalogData->isPriceGlobal() && $this->getWebsiteId()) {
            if (null !== $this->getData('tmp_selection_price_value')) {
                $this->setSelectionPriceValue($this->getData('tmp_selection_price_value'));
            }
            if (null !== $this->getData('tmp_selection_price_type')) {
                $this->setSelectionPriceType($this->getData('tmp_selection_price_type'));
            }
            $this->getResource()->saveSelectionPrice($this);

            if (!$this->getDefaultPriceScope()) {
                $this->unsSelectionPriceValue();
                $this->unsSelectionPriceType();
            }
        }
        return parent::afterSave();
    }
}
