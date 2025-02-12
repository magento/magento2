<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\MassAction;

use Magento\AdminNotification\Controller\Adminhtml\Notification\Remove;
use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class checks if remove action can be displayed on massaction list
 */
class RemoveVisibility implements VisibilityCheckerInterface
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
        return $this->authorization->isAllowed(Remove::ADMIN_RESOURCE);
    }
}
