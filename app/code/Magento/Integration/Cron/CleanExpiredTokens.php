<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Cron;

use Magento\Integration\Model\ResourceModel\Oauth\Token as TokenResourceModel;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

/**
 * Cron class for deleting expired OAuth tokens.
 * @since 2.2.0
 */
class CleanExpiredTokens
{
    /**
     * @var TokenResourceModel
     * @since 2.2.0
     */
    private $tokenResourceModel;

    /**
     * @var OauthHelper
     * @since 2.2.0
     */
    private $oauthHelper;

    /**
     * Initialize dependencies.
     *
     * @param TokenResourceModel $tokenResourceModel
     * @param OauthHelper $oauthHelper
     * @since 2.2.0
     */
    public function __construct(
        TokenResourceModel $tokenResourceModel,
        OauthHelper $oauthHelper
    ) {
        $this->tokenResourceModel = $tokenResourceModel;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * Delete expired customer and admin tokens.
     *
     * @return void
     * @since 2.2.0
     */
    public function execute()
    {
        $this->tokenResourceModel->deleteExpiredTokens(
            $this->oauthHelper->getAdminTokenLifetime(),
            [UserContextInterface::USER_TYPE_ADMIN]
        );
        $this->tokenResourceModel->deleteExpiredTokens(
            $this->oauthHelper->getCustomerTokenLifetime(),
            [UserContextInterface::USER_TYPE_CUSTOMER]
        );
    }
}
