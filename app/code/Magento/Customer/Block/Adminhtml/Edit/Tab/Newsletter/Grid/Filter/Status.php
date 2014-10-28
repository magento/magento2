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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter;

use Magento\Newsletter\Model\Queue;

/**
 * Adminhtml newsletter subscribers grid website filter
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = array(
            null => null,
            Queue::STATUS_SENT => __('Sent'),
            Queue::STATUS_CANCEL => __('Cancel'),
            Queue::STATUS_NEVER => __('Not Sent'),
            Queue::STATUS_SENDING => __('Sending'),
            Queue::STATUS_PAUSE => __('Paused')
        );
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = array();
        foreach (self::$_statuses as $status => $label) {
            $options[] = array('value' => $status, 'label' => __($label));
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return is_null($this->getValue()) ? null : array('eq' => $this->getValue());
    }
}
