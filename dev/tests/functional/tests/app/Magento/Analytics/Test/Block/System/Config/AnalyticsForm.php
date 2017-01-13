<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\System\Config;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Analytics form in admin configurations.
 *
 */
class AnalyticsForm extends Form
{
    /**
     * @var string
     */
    private $analyticsStatus = '#analytics_general_enabled';

    /**
     * @var string
     */
    private $analyticsStatusLabel = '#row_analytics_general_label .value';

    public function isAnalyticsEnabled()
    {
        return $this->_rootElement->find($this->analyticsStatus, Locator::SELECTOR_CSS)->getValue();
    }

    public function getAnalyticsStatus()
    {
        return $this->_rootElement->find($this->analyticsStatusLabel, Locator::SELECTOR_CSS)->getText();
    }
}
