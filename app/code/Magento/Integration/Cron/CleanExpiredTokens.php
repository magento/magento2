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
 */
class CleanExpiredTokens
{
    /**
     * @var TokenResourceModel
     */
    private $tokenResourceModel;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * Initialize dependencies.
     *
     * @param TokenResourceModel $tokenResourceModel
     * @param OauthHelper $oauthHelper
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
