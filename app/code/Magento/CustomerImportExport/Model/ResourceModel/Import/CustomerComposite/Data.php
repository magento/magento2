<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\Import\CustomerComposite;

class Data extends \Magento\ImportExport\Model\ResourceModel\Import\Data
{
    /**
     * Entity
     *
     * @var string
     */
    protected $_entityType = CustomerComposite::COMPONENT_ENTITY_CUSTOMER;

    /**
     * Customer attributes data
     *
     * @var array
     */
    protected $_customerAttributes = [];

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string $connectionName
     * @param array $arguments
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $connectionName = null,
        array $arguments = []
    ) {
        parent::__construct($context, $jsonHelper, $connectionName);

        if (isset($arguments['entity_type'])) {
            $this->_entityType = $arguments['entity_type'];
        }
        if (isset($arguments['customer_attributes'])) {
            $this->_customerAttributes = $arguments['customer_attributes'];
        }
    }

    /**
     * Get next bunch of validated rows.
     *
     * @param array|null $ids
     * @return array|null
     */
    public function getNextUniqueBunch($ids = null)
    {
        $bunchRows = parent::getNextUniqueBunch($ids);
        if ($bunchRows != null) {
            $rows = [];
            foreach ($bunchRows as $rowNumber => $rowData) {
                $rowData = $this->_prepareRow($rowData);
                if ($rowData !== null) {
                    unset($rowData['_scope']);
                    $rows[$rowNumber] = $rowData;
                }
            }
            return $rows;
        } else {
            return $bunchRows;
        }
    }

    /**
     * Prepare row
     *
     * @param array $rowData
     * @return array|null
     */
    protected function _prepareRow(array $rowData)
    {
        $entityCustomer = CustomerComposite::COMPONENT_ENTITY_CUSTOMER;
        if ($this->_entityType == $entityCustomer) {
            if ((isset($rowData['_scope'])) && $rowData['_scope'] == CustomerComposite::SCOPE_DEFAULT) {
                return $rowData;
            } else {
                return null;
            }
        } else {
            return $this->_prepareAddressRowData($rowData);
        }
    }

    /**
     * Prepare data row for address entity validation or import
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareAddressRowData(array $rowData)
    {
        $excludedAttributes = [
            CustomerComposite::COLUMN_DEFAULT_BILLING,
            CustomerComposite::COLUMN_DEFAULT_SHIPPING,
        ];
        $prefix = CustomerComposite::COLUMN_ADDRESS_PREFIX;

        $result = [];
        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes)) {
                if (!in_array($key, $excludedAttributes)) {
                    $key = str_replace($prefix, '', $key);
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
