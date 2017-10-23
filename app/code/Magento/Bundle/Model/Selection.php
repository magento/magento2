<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

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
     * Catalog data
     *
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
     * @return $this
     */
    public function afterSave()
    {
        if (!$this->_catalogData->isPriceGlobal() && $this->getWebsiteId()) {
            $this->getResource()->saveSelectionPrice($this);

            if (!$this->getDefaultPriceScope()) {
                $this->unsSelectionPriceValue();
                $this->unsSelectionPriceType();
            }
        }
        parent::afterSave();
    }
}
