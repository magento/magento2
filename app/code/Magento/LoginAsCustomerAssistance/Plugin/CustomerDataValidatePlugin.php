<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\LoginAsCustomerAssistance\Api\IsAssistanceEnabledInterface;
use Magento\LoginAsCustomerAssistance\Model\ResourceModel\GetLoginAsCustomerAssistanceAllowed;

/**
 * Check if User have permission to change Customers Opt-In preference.
 */
class CustomerDataValidatePlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var GetLoginAsCustomerAssistanceAllowed
     */
    private $getLoginAsCustomerAssistanceAllowed;

    /**
     * @param AuthorizationInterface $authorization
     * @param GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
     */
    public function __construct(
        AuthorizationInterface $authorization,
        GetLoginAsCustomerAssistanceAllowed $getLoginAsCustomerAssistanceAllowed
    ) {
        $this->authorization = $authorization;
        $this->getLoginAsCustomerAssistanceAllowed = $getLoginAsCustomerAssistanceAllowed;
    }

    /**
     * Check if User have permission to change Customers Opt-In preference.
     *
     * @param Form $subject
     * @param RequestInterface $request
     * @param null|string $scope
     * @param bool $scopeOnly
     * @throws \Magento\Framework\Validator\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExtractData(
        Form $subject,
        RequestInterface $request,
        $scope = null,
        $scopeOnly = true
    ): void {
        if ($this->isSetAssistanceAllowedParam($request)
            && !$this->authorization->isAllowed('Magento_LoginAsCustomer::allow_shopping_assistance')
        ) {
            $customerId = $request->getParam('customer_id');
            $assistanceAllowedParam =
                (int)$request->getParam('customer')['extension_attributes']['assistance_allowed'];
            $assistanceAllowed = $this->getLoginAsCustomerAssistanceAllowed->execute((int)$customerId);
            $assistanceAllowedStatus = $this->resolveStatus($assistanceAllowed);
            if ($this->isAssistanceAllowedChangeImportant($assistanceAllowedStatus, $assistanceAllowedParam)) {
                $errorMessages = [
                    MessageInterface::TYPE_ERROR => [
                        __(
                            'You have no permission to change Opt-In preference.'
                        ),
                    ],
                ];

                throw new \Magento\Framework\Validator\Exception(
                    null,
                    null,
                    $errorMessages
                );
            }
        }
    }

    /**
     * Check if assistance_allowed param is set.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isSetAssistanceAllowedParam(RequestInterface $request): bool
    {
        return is_array($request->getParam('customer'))
            && isset($request->getParam('customer')['extension_attributes'])
            && isset($request->getParam('customer')['extension_attributes']['assistance_allowed']);
    }

    /**
     * Check if change of assistance_allowed attribute is important.
     *
     * E. g. if assistance_allowed is going to be disabled while now it's enabled
     * or if it's going to be enabled while now it is disabled or not set at all.
     *
     * @param int $assistanceAllowed
     * @param int $assistanceAllowedParam
     * @return bool
     */
    private function isAssistanceAllowedChangeImportant(int $assistanceAllowed, int $assistanceAllowedParam): bool
    {
        $result = false;
        if (($assistanceAllowedParam === IsAssistanceEnabledInterface::DENIED
                && $assistanceAllowed === IsAssistanceEnabledInterface::ALLOWED)
            ||
            ($assistanceAllowedParam === IsAssistanceEnabledInterface::ALLOWED
                && $assistanceAllowed !== IsAssistanceEnabledInterface::ALLOWED)) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get integer status value from boolean.
     *
     * @param bool $assistanceAllowed
     * @return int
     */
    private function resolveStatus(bool $assistanceAllowed): int
    {
        return $assistanceAllowed ? IsAssistanceEnabledInterface::ALLOWED : IsAssistanceEnabledInterface::DENIED;
    }
}
