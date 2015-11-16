<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Login page for backend.
 */
class AdminAuthLogin extends Page
{
    /**
     * URL part for backend authorization.
     */
    const MCA = 'admin/auth/login';

    /**
     * Form for login.
     *
     * @var string
     */
    protected $loginBlock = '#login-form';

    /**
     * Header panel of admin dashboard.
     *
     * @var string
     */
    protected $headerBlock = '.page-header .admin-user';

    /**
     * Global messages block.
     *
     * @var string
     */
    protected $messagesBlock = '.messages';

    /**
     * Constructor.
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get the login form block.
     *
     * @return \Magento\Backend\Test\Block\Admin\Login
     */
    public function getLoginBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendAdminLogin(
            $this->browser->find($this->loginBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get the header panel block of admin dashboard.
     *
     * @return \Magento\Backend\Test\Block\Page\Header
     */
    public function getHeaderBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendPageHeader(
            $this->browser->find($this->headerBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get global messages block.
     *
     * @return \Magento\Backend\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendMessages($this->browser->find($this->messagesBlock));
    }

    /**
     * Wait for Header block is visible in the page.
     *
     * @return void
     */
    public function waitForHeaderBlock()
    {
        $browser = $this->browser;
        $selector = $this->headerBlock;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $item = $browser->find($selector);
                return $item->isVisible() ? true : null;
            }
        );
    }
}
