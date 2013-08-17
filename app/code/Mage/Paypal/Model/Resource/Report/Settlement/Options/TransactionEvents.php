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
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Transaction Events Types Options
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Resource_Report_Settlement_Options_TransactionEvents
    implements Mage_Core_Model_Option_ArrayInterface
{
    /**
     * @var Mage_Paypal_Model_Report_Settlement_Row
     */
    protected $_model;

    /**
     * @param Mage_Paypal_Model_Report_Settlement_Row $model
     */
    public function __construct(Mage_Paypal_Model_Report_Settlement_Row $model)
    {
        $this->_model = $model;
    }

    /**
     *  Get full list of codes with their description
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_model->getTransactionEvents();
    }
}
