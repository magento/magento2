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
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order transactions tab
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Transactions extends \Magento\Sales\Block\Adminhtml\Transactions\Grid implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Retrieve grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales/order/transactions', array('_current' => true));
    }

    /**
     * Retrieve grid row url
     *
     * @param \Magento\Object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('sales/transactions/view', array('_current' => true, 'txn_id' => $item->getId()));
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Transactions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Transactions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return !$this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
    }
}
