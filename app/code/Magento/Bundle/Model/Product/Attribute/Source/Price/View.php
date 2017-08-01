<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\Attribute\Source\Price;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

/**
 * Bundle Price View Attribute Renderer
 *
 * @api
 * @since 2.0.0
 */
class View extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var OptionFactory
     * @since 2.0.0
     */
    protected $optionFactory;

    /**
     * @param OptionFactory $optionFactory
     * @since 2.0.0
     */
    public function __construct(OptionFactory $optionFactory)
    {
        $this->optionFactory = $optionFactory;
    }

    /**
     * Get all options
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Price Range'), 'value' => 0],
                ['label' => __('As Low as'), 'value' => 1],
            ];
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     * @since 2.0.0
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     * @since 2.0.0
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Bundle Price View ' . $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Select for update Attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     * @since 2.0.0
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->optionFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
