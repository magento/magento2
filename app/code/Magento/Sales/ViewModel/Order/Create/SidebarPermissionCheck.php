<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Order\Create;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Sidebar block permission check
 */
class SidebarPermissionCheck implements ArgumentInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Permissions constructor.
     *
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * To check customer permission
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->authorization->isAllowed('Magento_Customer::customer');
    }
}
