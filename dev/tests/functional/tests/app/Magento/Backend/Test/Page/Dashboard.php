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

namespace Magento\Backend\Test\Page;

use Mtf\Factory\Factory;
use Mtf\Page\Page;
use Mtf\Client\Element\Locator;

/**
 * Class Dashboard
 * Dashboard (Home) page for backend
 *
 */
class Dashboard extends Page
{
    /**
     * URL part for backend authorization
     */
    const MCA = 'admin/dashboard';

    /**
     * Header panel block of dashboard page
     *
     * @var string
     */
    protected $adminPanelHeader = 'page-header';

    /**
     * Page title block
     *
     * @var string
     */
    protected $titleBlock = '.page-title';

    /**
     * Top menu selector
     *
     * @var string
     */
    protected $menuBlock = '.navigation';

    /**
     * Constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get admin panel header block instance
     *
     * @return \Magento\Backend\Test\Block\Page\Header
     */
    public function getAdminPanelHeader()
    {
        return Factory::getBlockFactory()->getMagentoBackendPageHeader(
            $this->_browser->find($this->adminPanelHeader, Locator::SELECTOR_CLASS_NAME)
        );
    }

    /**
     * Get title block
     *
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return Factory::getBlockFactory()->getMagentoThemeHtmlTitle(
            $this->_browser->find($this->titleBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Menu block
     *
     * @return \Magento\Backend\Test\Block\Menu
     */
    public function getMenuBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendMenu(
            $this->_browser->find($this->menuBlock, Locator::SELECTOR_CSS)
        );
    }
}
