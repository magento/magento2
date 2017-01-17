<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Product\Attribute\Source\Type;

/**
 * Source model for 'msrp_display_actual_price_type' product attribute
 */
class Price extends \Magento\Msrp\Model\Product\Attribute\Source\Type
{
    /**
     * Get value from the store configuration settings
     */
    const TYPE_USE_CONFIG = 0;

    /**
     * Entity attribute factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    protected $entityAttributeFactory;

    /**
     * Eav resource helper
     *
     * @var \Magento\Eav\Model\ResourceModel\Helper
     */
    protected $eavResourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $entityAttributeFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $eavResourceHelper
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $entityAttributeFactory,
        \Magento\Eav\Model\ResourceModel\Helper $eavResourceHelper
    ) {
        $this->entityAttributeFactory = $entityAttributeFactory;
        $this->eavResourceHelper = $eavResourceHelper;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                [['label' => __('Use config'), 'value' => self::TYPE_USE_CONFIG]],
                parent::getAllOptions()
            );
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeType = $this->getAttribute()->getBackendType();
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => $this->eavResourceHelper->getDdlTypeByColumnType($attributeType),
                'nullable' => true,
            ],
        ];
    }

    /**
     * Retrieve select for flat attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->entityAttributeFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
