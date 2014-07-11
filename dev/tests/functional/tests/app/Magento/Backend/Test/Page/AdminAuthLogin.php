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

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;

/**
 * Class AdminAuthLogin
 * Login page for backend
 *
 */
class AdminAuthLogin extends Page
{
    /**
     * URL part for backend authorization
     */
    const MCA = 'admin/auth/login';

    /**
     * Form for login
     *
     * @var string
     */
    protected $loginBlock = '#login-form';

    /**
     * Header panel of admin dashboard
     *
     * @var string
     */
    protected $headerBlock = '.page-header .admin-user';

    /**
     * Global messages block
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Constructor
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get the login form block
     *
     * @return \Magento\Backend\Test\Block\Admin\Login
     */
    public function getLoginBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendAdminLogin(
            $this->_browser->find($this->loginBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get the header panel block of admin dashboard
     *
     * @return \Magento\Backend\Test\Block\Page\Header
     */
    public function getHeaderBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendPageHeader(
            $this->_browser->find($this->headerBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get global messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find($this->messagesBlock));
    }

    public function waitForHeaderBlock()
    {
        $browser = $this->_browser;
        $selector = $this->headerBlock;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $item = $browser->find($selector);
                return $item->isVisible() ? true : null;
            }
        );
    }
}
