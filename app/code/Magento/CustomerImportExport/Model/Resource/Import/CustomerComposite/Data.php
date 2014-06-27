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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CustomerImportExport\Model\Resource\Import\CustomerComposite;

use Magento\CustomerImportExport\Model\Import\CustomerComposite;

class Data extends \Magento\ImportExport\Model\Resource\Import\Data
{
    /**
     * Entity type
     *
     * @var string
     */
    protected $_entityType = CustomerComposite::COMPONENT_ENTITY_CUSTOMER;

    /**
     * Customer attributes
     *
     * @var array
     */
    protected $_customerAttributes = array();

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $arguments
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Core\Helper\Data $coreHelper,
        array $arguments = array()
    ) {
        parent::__construct($resource, $coreHelper, $arguments);

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
     * @return array|null
     */
    public function getNextBunch()
    {
        $bunchRows = parent::getNextBunch();
        if ($bunchRows != null) {
            $rows = array();
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
            if ($rowData['_scope'] == CustomerComposite::SCOPE_DEFAULT) {
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
        $excludedAttributes = array(
            CustomerComposite::COLUMN_DEFAULT_BILLING,
            CustomerComposite::COLUMN_DEFAULT_SHIPPING
        );
        $prefix = CustomerComposite::COLUMN_ADDRESS_PREFIX;

        $result = array();
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
