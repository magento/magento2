<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Helper;

use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random;

/**
 * User data helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'admin/security/password_reset_link_expiration_period';

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * @param Context $context
     * @param ConfigInterface $config
     * @param Random $mathRandom
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        protected readonly Random $mathRandom
    ) {
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generateResetPasswordLinkToken()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->_config->getValue(self::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD);
    }
}
