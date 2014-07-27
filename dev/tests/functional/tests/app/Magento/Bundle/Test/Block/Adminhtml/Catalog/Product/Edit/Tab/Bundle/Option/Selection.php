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

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

use Mtf\Block\Form;

/**
 * Class Selection
 * Assigned product row to bundle option
 */
class Selection extends Form
{
    /**
     * Fill data to product row
     *
     * @param array $fields
     * @return void
     */
    public function fillProductRow(array $fields)
    {
        unset($fields['product_id']);
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Get data item selection
     *
     * @param array $fields
     * @return array
     */
    public function getProductRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $newFields = $this->_getData($mapping);
        if (isset($mapping['getProductName'])) {
            $newFields['getProductName'] = $this->_rootElement->find(
                $mapping['getProductName']['selector'],
                $mapping['getProductName']['strategy']
            )->getText();
        }
        return $newFields;
    }
}
