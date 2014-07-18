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

use Mtf\Page\BackendPage;

/**
 * Class Dashboard
 * Dashboard (Home) page for backend
 */
class Dashboard extends BackendPage
{
    /**
     * URL part for backend authorization
     */
    const MCA = 'admin/dashboard';

    protected $_blocks = [
        'adminPanelHeader' => [
            'name' => 'adminPanelHeader',
            'class' => 'Magento\Backend\Test\Block\Page\Header',
            'locator' => '.page-header',
            'strategy' => 'css selector',
        ],
        'titleBlock' => [
            'name' => 'titleBlock',
            'class' => 'Magento\Theme\Test\Block\Html\Title',
            'locator' => '.page-title',
            'strategy' => 'css selector',
        ],
        'menuBlock' => [
            'name' => 'menuBlock',
            'class' => 'Magento\Backend\Test\Block\Menu',
            'locator' => '.navigation',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * Get admin panel header block instance
     *
     * @return \Magento\Backend\Test\Block\Page\Header
     */
    public function getAdminPanelHeader()
    {
        return $this->getBlockInstance('adminPanelHeader');
    }

    /**
     * Get title block
     *
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return $this->getBlockInstance('titleBlock');
    }

    /**
     * Get Menu block
     *
     * @return \Magento\Backend\Test\Block\Menu
     */
    public function getMenuBlock()
    {
        return $this->getBlockInstance('menuBlock');
    }
}
