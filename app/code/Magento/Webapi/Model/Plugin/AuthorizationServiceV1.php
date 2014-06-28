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
namespace Magento\Webapi\Model\Plugin;

use Magento\Authz\Model\UserIdentifier;
use Magento\Integration\Service\V1\Integration as IntegrationService;
use Magento\Integration\Model\Integration;
use Magento\Framework\Logger;

/**
 * Wrap isAllowed() method from AuthorizationV1 service to avoid checking roles of deactivated integration.
 */
class AuthorizationServiceV1
{
    /** @var IntegrationService */
    protected $_integrationService;

    /** @var Logger */
    protected $_logger;

    /** @var UserIdentifier */
    protected $_userIdentifier;

    /**
     * Inject dependencies.
     *
     * @param IntegrationService $integrationService
     * @param Logger             $logger
     * @param UserIdentifier     $userIdentifier
     */
    public function __construct(IntegrationService $integrationService, Logger $logger, UserIdentifier $userIdentifier)
    {
        $this->_integrationService = $integrationService;
        $this->_logger = $logger;
        $this->_userIdentifier = $userIdentifier;
    }

    /**
     * Check whether integration is inactive and don't allow using this integration in this case.
     *
     * It's ok that we break invocation chain since we're dealing with ACL here - if something is not allowed at any
     * point it couldn't be made allowed at some other point.
     *
     * @param \Magento\Authz\Service\AuthorizationV1 $subject
     * @param callable $proceed
     * @param mixed $resources
     * @param UserIdentifier $userIdentifier
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Authz\Service\AuthorizationV1 $subject,
        \Closure $proceed,
        $resources,
        \Magento\Authz\Model\UserIdentifier $userIdentifier = null
    ) {
        /** @var UserIdentifier $userIdentifierObject */
        $userIdentifierObject = $userIdentifier ?: $this->_userIdentifier;

        if ($userIdentifierObject->getUserType() !== UserIdentifier::USER_TYPE_INTEGRATION) {
            return $proceed($resources, $userIdentifier);
        }

        try {
            $integration = $this->_integrationService->get($userIdentifierObject->getUserId());
        } catch (\Exception $e) {
            // Wrong integration ID or DB not reachable or whatever - give up and don't allow just in case
            $this->_logger->logException($e);
            return false;
        }

        if ($integration->getStatus() !== Integration::STATUS_ACTIVE) {
            return false;
        }

        return $proceed($resources, $userIdentifier);
    }
}
