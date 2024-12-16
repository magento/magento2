<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\MassAction;

use Magento\AdminNotification\Controller\Adminhtml\Notification\MarkAsRead;
use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class checks if mark as read action can be displayed on massaction list
 */
class MarkAsReadVisibility implements VisibilityCheckerInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param AuthorizationInterface $authorizationInterface
     */
    public function __construct(AuthorizationInterface $authorizationInterface)
    {
        $this->authorization = $authorizationInterface;
    }

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        return $this->authorization->isAllowed(MarkAsRead::ADMIN_RESOURCE);
    }
}
