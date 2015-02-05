<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Install block.
 */
class Install extends Block
{
    /**
     * 'Install Now' button.
     *
     * @var string
     */
    protected $installNow = "//*[@ng-show='!isStarted']/button";

    /**
     * Admin info block.
     *
     * @var string
     */
    protected $adminInfo = "//*[@id='admin-info']";

    /**
     * Database info block.
     *
     * @var string
     */
    protected $dbInfo = "//*[@id='db-info']";

    /**
     * 'Launch Magento Admin' button.
     *
     * @var string
     */
    protected $launchAdmin = '.btn-large.btn-prime';

    /**
     * Click on 'Install Now' button.
     *
     * @return void
     */
    public function clickInstallNow()
    {
        $this->_rootElement->find($this->installNow, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementVisible($this->launchAdmin, Locator::SELECTOR_CSS);
    }

    /**
     * Get admin info.
     *
     * @return string
     */
    public function getAdminInfo()
    {
        $adminData = [];
        $rows = $this->_rootElement->getElements('#admin-info .row');
        foreach ($rows as $row) {
            $dataRow = $row->getElements('div');
            $key = strtolower(str_replace(' ', '_', str_replace(':', '', $dataRow[0]->getText())));
            $adminData[$key] = $dataRow[1]->getText();
        }

        return $adminData;
    }

    /**
     * Get database info.
     *
     * @return string
     */
    public function getDbInfo()
    {
        $dbData = [];
        $rows = $this->_rootElement->getElements('#db-info .row');
        foreach ($rows as $row) {
            $dataRow = $row->getElements('div');
            $key = strtolower(str_replace(' ', '_', str_replace(':', '', $dataRow[0]->getText())));
            $dbData[$key] = $dataRow[1]->getText();
        }

        return $dbData;
    }

    /**
     * Click on 'Launch Magento Admin' button.
     *
     * @return void
     */
    public function clickLaunchAdmin()
    {
        $this->_rootElement->find($this->launchAdmin, Locator::SELECTOR_XPATH)->click();
    }
}
