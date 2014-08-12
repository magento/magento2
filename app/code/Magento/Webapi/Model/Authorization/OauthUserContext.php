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

namespace Magento\Webapi\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Service\V1\Integration as IntegrationService;
use Magento\Webapi\Controller\Request;
use Magento\Framework\Oauth\Helper\Request as OauthRequestHelper;
use Magento\Framework\Oauth\OauthInterface as OauthService;

/**
 * A user context determined by OAuth headers in a HTTP request.
 */
class OauthUserContext implements UserContextInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var IntegrationService
     */
    protected $integrationService;

    /**
     * @var OauthService
     */
    protected $oauthService;

    /**
     * @var  OauthRequestHelper
     */
    protected $oauthHelper;

    /**
     * @var int
     */
    protected $integrationId;

    /**
     * Initialize dependencies.
     *
     * @param Request $request
     * @param IntegrationService $integrationService
     * @param OauthService $oauthService
     * @param OauthRequestHelper $oauthHelper
     */
    public function __construct(
        Request $request,
        IntegrationService $integrationService,
        OauthService $oauthService,
        OauthRequestHelper $oauthHelper
    ) {
        $this->request = $request;
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        if ($this->integrationId) {
            return $this->integrationId;
        }
        $oauthRequest = $this->oauthHelper->prepareRequest($this->request);
        //If its not a valid Oauth request no further processing is needed
        if (empty($oauthRequest)) {
            return null;
        }
        $consumerId = $this->oauthService->validateAccessTokenRequest(
            $oauthRequest,
            $this->oauthHelper->getRequestUrl($this->request),
            $this->request->getMethod()
        );
        $integration = $this->integrationService->findActiveIntegrationByConsumerId($consumerId);
        return $this->integrationId = ($integration->getId() ? (int)$integration->getId() : null);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_INTEGRATION;
    }
}
