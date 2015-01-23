<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Test\Block\Dashboard;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\FormTabs;

class StoreStats extends FormTabs
{
    /**
     * Refresh data button
     *
     * @var string
     */
    protected $refreshData = '//button[@title="Refresh data"]';

    /**
     * Click Refresh Data button
     *
     * return void
     */
    public function refreshData()
    {
        $this->_rootElement->find($this->refreshData, Locator::SELECTOR_XPATH)->click();
    }
}
