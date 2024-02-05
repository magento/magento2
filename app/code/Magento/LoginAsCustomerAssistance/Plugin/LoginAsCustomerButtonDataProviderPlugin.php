<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;
use Magento\LoginAsCustomerAssistance\Model\IsCustomerEnabled;

/**
 * Change Login as Customer button behavior if Customer has not granted permission.
 */
class LoginAsCustomerButtonDataProviderPlugin
{
    /**
     * @var IsAssistanceEnabled
     */
    private $isAssistanceEnabled;

    /**
     * @var IsCustomerEnabled
     */
    private $isCustomerEnabled;

    /**
     * @param IsAssistanceEnabled $isAssistanceEnabled
     * @param IsCustomerEnabled $isCustomerEnabled
     */
    public function __construct(
        IsAssistanceEnabled $isAssistanceEnabled,
        IsCustomerEnabled $isCustomerEnabled
    ) {
        $this->isAssistanceEnabled = $isAssistanceEnabled;
        $this->isCustomerEnabled = $isCustomerEnabled;
    }

    /**
     * Change Login as Customer button behavior if Customer has not granted permission.
     *
     * @param DataProvider $subject
     * @param array $result
     * @param int $customerId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(DataProvider $subject, array $result, int $customerId): array
    {
        if (isset($result['on_click'])
            && !($this->isCustomerEnabled->execute($customerId) && $this->isAssistanceEnabled->execute($customerId))
        ) {
            $result['on_click'] = 'window.lacNotAllowedPopup()';
        }

        return $result;
    }
}
