<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Page;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

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
