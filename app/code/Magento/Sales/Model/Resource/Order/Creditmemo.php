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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order creditmemo resource
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Resource\Order;

class Creditmemo extends \Magento\Sales\Model\Resource\Order\AbstractOrder
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix                  = 'sales_order_creditmemo_resource';

    /**
     * Is grid available
     *
     * @var bool
     */
    protected $_grid                         = true;

    /**
     * Flag for using of increment id
     *
     * @var bool
     */
    protected $_useIncrementId               = true;

    /**
     * Entity code for increment id (Eav entity code)
     *
     * @var string
     */
    protected $_entityTypeForIncrementId     = 'creditmemo';

    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('sales_flat_creditmemo', 'entity_id');
    }

    /**
     * Init virtual grid records for entity
     *
     * @return \Magento\Sales\Model\Resource\Order\Creditmemo
     */
    protected function _initVirtualGridColumns()
    {
        parent::_initVirtualGridColumns();
        $adapter          = $this->getReadConnection();
        $checkedFirstname = $adapter->getIfNullSql('{{table}}.firstname', $adapter->quote(''));
        $checkedLastname  = $adapter->getIfNullSql('{{table}}.lastname', $adapter->quote(''));
        $concatName       = $adapter->getConcatSql(array($checkedFirstname, $adapter->quote(' '), $checkedLastname));

        $this->addVirtualGridColumn(
            'billing_name',
            'sales_flat_order_address',
            array('billing_address_id' => 'entity_id'),
            $concatName
        )
        ->addVirtualGridColumn(
            'order_increment_id',
            'sales_flat_order',
            array('order_id' => 'entity_id'),
            'increment_id'
        )
        ->addVirtualGridColumn(
            'order_created_at',
            'sales_flat_order',
            array('order_id' => 'entity_id'),
            'created_at'
        );

        return $this;
    }
}
