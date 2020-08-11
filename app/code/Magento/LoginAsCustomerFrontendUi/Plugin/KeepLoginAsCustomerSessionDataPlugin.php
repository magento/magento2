<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LoginAsCustomerFrontendUi\Plugin;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\GetLoggedAsCustomerAdminIdInterface;
use Magento\LoginAsCustomerApi\Api\SetLoggedAsCustomerAdminIdInterface;

/**
 * Keep adminId in customer session if session data is cleared.
 */
class KeepLoginAsCustomerSessionDataPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetLoggedAsCustomerAdminIdInterface
     */
    private $getLoggedAsCustomerAdminId;

    /**
     * @var SetLoggedAsCustomerAdminIdInterface
     */
    private $setLoggedAsCustomerAdminId;

    /**
     * @param ConfigInterface $config
     * @param GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId
     * @param SetLoggedAsCustomerAdminIdInterface $setLoggedAsCustomerAdminId
     */
    public function __construct(
        ConfigInterface $config,
        GetLoggedAsCustomerAdminIdInterface $getLoggedAsCustomerAdminId,
        SetLoggedAsCustomerAdminIdInterface $setLoggedAsCustomerAdminId
    ) {
        $this->config = $config;
        $this->getLoggedAsCustomerAdminId = $getLoggedAsCustomerAdminId;
        $this->setLoggedAsCustomerAdminId = $setLoggedAsCustomerAdminId;
    }

    /**
     * Keep adminId in customer session if session data is cleared.
     *
     * @param SessionManagerInterface $subject
     * @param \Closure $proceed
     * @return SessionManagerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundClearStorage(
        SessionManagerInterface $subject,
        \Closure $proceed
    ): SessionManagerInterface {
        $enabled = $this->config->isEnabled();
        $adminId = $enabled ? $this->getLoggedAsCustomerAdminId->execute() : null;
        $result = $proceed();
        if ($enabled && $adminId) {
            $this->setLoggedAsCustomerAdminId->execute($adminId);
        }

        return $result;
    }
}
