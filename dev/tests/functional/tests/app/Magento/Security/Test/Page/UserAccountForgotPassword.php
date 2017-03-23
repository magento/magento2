<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Page;

use Magento\Mtf\Page\Page;

/**
 * Class UserAccountForgotPassword
 */
class UserAccountForgotPassword extends Page
{
    const MCA = 'admin/auth/forgotpassword';

    /**
     * Blocks' config
     *
     * @var array
     */
    protected $blocks = [
        'messagesBlock' => [
            'class' => \Magento\Backend\Test\Block\Messages::class,
            'locator' => '.messages',
            'strategy' => 'css selector',
        ],
        'forgotPasswordForm' => [
            'class' => \Magento\Security\Test\Block\Form\ForgotPassword::class,
            'locator' => '#login-form',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * Constructor.
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * @return \Magento\Backend\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getBlockInstance('messagesBlock');
    }

    /**
     * @return \Magento\Security\Test\Block\Form\ForgotPassword
     */
    public function getForgotPasswordForm()
    {
        return $this->getBlockInstance('forgotPasswordForm');
    }
}
