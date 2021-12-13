<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

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
     * @param IsAssistanceEnabled $isAssistanceEnabled
     */
    public function __construct(
        IsAssistanceEnabled $isAssistanceEnabled
    ) {
        $this->isAssistanceEnabled = $isAssistanceEnabled;
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
        if (isset($result['on_click']) && !$this->isAssistanceEnabled->execute($customerId)) {
            $result['on_click'] = 'window.lacNotAllowedPopup()';
        }

        return $result;
    }
}
