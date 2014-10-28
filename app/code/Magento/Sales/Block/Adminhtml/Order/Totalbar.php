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
namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Adminhtml creditmemo bar
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Totalbar extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * Totals
     *
     * @var array
     */
    protected $_totals = array();

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the parent block for this block.'));
        }
        $this->setOrder($this->getParentBlock()->getOrder());
        $this->setSource($this->getParentBlock()->getSource());
        $this->setCurrency($this->getParentBlock()->getOrder()->getOrderCurrency());

        foreach ($this->getParentBlock()->getOrderTotalbarData() as $v) {
            $this->addTotal($v[0], $v[1], $v[2]);
        }

        parent::_beforeToHtml();
    }

    /**
     * Get totals
     *
     * @return array
     */
    protected function getTotals()
    {
        return $this->_totals;
    }

    /**
     * Add total
     *
     * @param string $label
     * @param float $value
     * @param bool $grand
     * @return $this
     */
    public function addTotal($label, $value, $grand = false)
    {
        $this->_totals[] = array('label' => $label, 'value' => $value, 'grand' => $grand);
        return $this;
    }
}
