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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\Page;

use Mtf\Page\Page;
use Mtf\Factory\Factory;

/**
 * Frontend Customer Dashboard page
 *
 */
class CustomerAccountIndex extends Page
{
    /**
     * URL for customer Dashboard
     */
    const MCA = 'customer/account/index';

    /**
     * Messages block
     *
     * @var string
     */
    protected $messagesBlock = '.page.messages';

    /**
     * Address Book block
     *
     * @var string
     */
    protected $dashboardAddressBlock = '.block.dashboard.addresses';

    /**
     * Dashboard title
     *
     * @var string
     */
    protected $titleBlock = '.page.title';

    /**
     * Account menu selector
     *
     * @var string
     */
    protected $accountMenuSelector = '.nav.items';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Get Messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessages()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find($this->messagesBlock));
    }

    /**
     * Get Address Book block
     *
     * @return \Magento\Customer\Test\Block\Account\Dashboard\Address
     */
    public function getDashboardAddress()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAccountDashboardAddress(
            $this->_browser->find($this->dashboardAddressBlock)
        );
    }

    /**
     * Get title block
     *
     * @return \Magento\Theme\Test\Block\Html\Title
     */
    public function getTitleBlock()
    {
        return $this->titleBlock = Factory::getBlockFactory()->getMagentoThemeHtmlTitle(
            $this->_browser->find($this->titleBlock)
        );
    }

    /**
     * Get Account Menu Block
     *
     * @return \Magento\Customer\Test\Block\Account\Menu
     */
    public function getAccountMenuBlock()
    {
        return Factory::getBlockFactory()->getMagentoCustomerAccountMenu(
            $this->_browser->find($this->accountMenuSelector)
        );
    }
}
