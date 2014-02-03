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
 * Sales orders grid massaction items updater
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Grid\Massaction;

class ItemsUpdater implements \Magento\Core\Model\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * Mass actions list in the form 'mass action name' => 'acl resource name'
     *
     * @var array
     */
    protected $_items = array(
        'print_invoices'        => 'Magento_Sales::print',
        'print_shipments'       => 'Magento_Sales::print',
        'print_credit_memos'    => 'Magento_Sales::print',
        'print_shipping_labels' => 'Magento_Sales::print',
        'print_all'             => 'Magento_Sales::print',
    );

    /**
     * @param \Magento\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\AuthorizationInterface $authorization)
    {
        $this->_authorization = $authorization;
    }

    /**
     * Remove mass action items in case they aren't allowed for current user
     *
     * @param mixed $argument
     * @return mixed
     */
    public function update($argument)
    {
        foreach ($this->_items as $itemName => $aclResourceName) {
            if (false === $this->_authorization->isAllowed($aclResourceName)) {
                unset($argument[$itemName]);
            }
        }

        return $argument;
    }
}
