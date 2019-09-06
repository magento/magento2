<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    private $analyticsStatusLabel = '#row_analytics_general_enabled > td.value > p > span';

    /**
     * @var string
     */
    private $submitButton = '#save';

    /**
     * @var string
     */
    private $analyticsVertical = '#analytics_general_vertical';

    /**
     * @var string
     */
    private $analyticsVerticalScope = '#row_analytics_general_vertical span[data-config-scope="[WEBSITE]"]';

    /**
     * @var string
     */
    private $sendDataTimeHh = '#row_analytics_general_collection_time > td.value > select:nth-child(2)';

    /**
     * @var string
     */
    private $sendDataTimeMm = '#row_analytics_general_collection_time > td.value > select:nth-child(3)';

    /**
     * @var string
     */
    private $sendDataTimeSs = '#row_analytics_general_collection_time > td.value > select:nth-child(4)';

    /**
     * @var string
     */
    private $timeZone =
        '#row_analytics_general_collection_time > td.value > p > span';

    /**
     * @return array|string
     */
    public function isAnalyticsEnabled()
    {
        return $this->_rootElement->find($this->analyticsStatus, Locator::SELECTOR_CSS)->getValue();
    }

    /**
     * @param string $state
     * @return array|string
     */
    public function analyticsToggle($state = 'Enable')
    {
        return $this->_rootElement->find($this->analyticsStatus, Locator::SELECTOR_CSS, 'select')->setValue($state);
    }

    /**
     * @return array|string
     */
    public function saveConfig()
    {
        return $this->browser->find($this->submitButton)->click();
    }

    /**
     * @return array|string
     */
    public function getAnalyticsStatus()
    {
        return $this->_rootElement->find($this->analyticsStatusLabel, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * @param string $vertical
     * @return array|string
     */
    public function setAnalyticsVertical($vertical)
    {
        return $this->_rootElement->find($this->analyticsVertical, Locator::SELECTOR_CSS, 'select')
            ->setValue($vertical);
    }

    /**
     * @param string $hh
     * @param string $mm
     * @return $this
     */
    public function setTimeOfDayToSendData($hh, $mm)
    {
        $this->_rootElement->find($this->sendDataTimeHh, Locator::SELECTOR_CSS, 'select')
            ->setValue($hh);
        $this->_rootElement->find($this->sendDataTimeMm, Locator::SELECTOR_CSS, 'select')
            ->setValue($mm);
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeOfDayToSendDate()
    {
        $hh = $this->_rootElement->find($this->sendDataTimeHh, Locator::SELECTOR_CSS, 'select')
            ->getValue();
        $mm = $this->_rootElement->find($this->sendDataTimeMm, Locator::SELECTOR_CSS, 'select')
            ->getValue();
        $ss = $this->_rootElement->find($this->sendDataTimeSs, Locator::SELECTOR_CSS, 'select')
            ->getValue();
        return sprintf('%s, %s, %s', $hh, $mm, $ss);
    }

    /**
     * @return mixed
     */
    public function getTimeZone()
    {
        return $this->_rootElement->find($this->timeZone, Locator::SELECTOR_CSS)
            ->getText();
    }

    /**
     * @return array|string
     */
    public function getAnalyticsVertical()
    {
        return $this->_rootElement->find($this->analyticsVertical, Locator::SELECTOR_CSS)->getValue();
    }

    /**
     * @return array|string
     */
    public function getAnalyticsVerticalScope()
    {
        return $this->_rootElement->find($this->analyticsVerticalScope, Locator::SELECTOR_CSS)->isVisible();
    }
}
