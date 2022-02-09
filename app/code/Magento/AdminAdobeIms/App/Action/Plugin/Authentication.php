<?php

namespace Magento\AdminAdobeIms\App\Action\Plugin;

class Authentication extends \Magento\Backend\App\Action\Plugin\Authentication
{
    /**
     * add adobe_ims_auth to allowed actions, so that we can call this controller in the admin login page
     *
     * @var string[]
     */
    protected $_openActions = [
        'adobe_ims_auth',
        'forgotpassword',
        'resetpassword',
        'resetpasswordpost',
        'logout',
        'refresh', // captcha refresh
    ];
}
