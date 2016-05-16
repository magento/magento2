<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    protected $adminInfo = '#admin-info';

    /**
     * Database info block.
     *
     * @var string
     */
    protected $dbInfo = '#db-info';

    /**
     * 'Launch Magento Admin' button.
     *
     * @var string
     */
    protected $launchAdmin = '.btn-large.btn-prime';

    /**
     * Text for installation is completed.
     *
     * @var string
     */
    private $successInstallText = "//p[contains(., 'Installing... 100%')]";

    /**
     * Click on 'Install Now' button.
     *
     * @return void
     */
    public function clickInstallNow()
    {
        $this->_rootElement->find($this->installNow, Locator::SELECTOR_XPATH)->click();
        $this->waitSuccessInstall();
    }

    /**
     * Get admin info.
     *
     * @return array
     */
    public function getAdminInfo()
    {
        return $this->getTableDataByCssLocator($this->adminInfo);
    }

    /**
     * Get database info.
     *
     * @return array
     */
    public function getDbInfo()
    {
        return $this->getTableDataByCssLocator($this->dbInfo);
    }

    /**
     * Get table data by correspondent div css selector.
     * Data inside the table must be presented via <dt>/<dd>/<dl> tags due to actual HTML5 standard.
     *
     * @param string $selector
     * @return array
     */
    protected function getTableDataByCssLocator($selector)
    {
        $data = [];
        $keys = [];
        $definitionTitles = $this->_rootElement->getElements($selector . ' dt');
        foreach ($definitionTitles as $dt) {
            $keys[] = strtolower(str_replace(' ', '_', str_replace(':', '', $dt->getText())));
        }
        reset($keys);

        $definitionDescriptions = $this->_rootElement->getElements($selector . ' dd');
        foreach ($definitionDescriptions as $dd) {
            $data[current($keys)] = $dd->getText();
            next($keys);
        }

        return $data;
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

    /**
     * Check that success install text is visible.
     *
     * @return bool
     */
    public function isInstallationCompleted()
    {
        return $this->_rootElement->find($this->successInstallText, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Waiting for success install text.
     *
     * @return void
     */
    private function waitSuccessInstall()
    {
        $root = $this->_rootElement;
        $successInstallText = $this->successInstallText;
        $launchAdmin = $this->launchAdmin;

        $root->waitUntil(
            function () use ($root, $successInstallText, $launchAdmin) {
                $isInstallText = $root->find($successInstallText, Locator::SELECTOR_XPATH)->isVisible();
                $isLaunchAdmin = $root->find($launchAdmin, Locator::SELECTOR_CSS)->isVisible();
                return $isInstallText == true || $isLaunchAdmin == true ? true : null;
            }
        );
    }
}
