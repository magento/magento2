<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Helper;

/**
 * User data helper
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'admin/security/password_reset_link_expiration_period';

    /**
     * @var \Magento\Backend\App\ConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Math\Random $mathRandom
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->_config = $config;
        $this->mathRandom = $mathRandom;
        parent::__construct($context);
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     * @since 2.0.0
     */
    public function generateResetPasswordLinkToken()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     * @since 2.0.0
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_config->getValue(self::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD);
    }
}
