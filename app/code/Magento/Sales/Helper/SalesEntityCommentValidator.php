<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Helper;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\CreditmemoCommentInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterface;

/**
 * Sales module base helper
 */
class SalesEntityCommentValidator extends AbstractHelper
{
    /**
     * UserContextInterface
     *
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @param Context $context
     * @param UserContextInterface $userContext
     */
    public function __construct(
        Context $context,
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
        parent::__construct(
            $context
        );
    }

    /**
     * Check whether sales entity comments are allowed to edit or not
     *
     * @param CreditmemoCommentInterface|InvoiceCommentInterface|ShipmentCommentInterface $salesEntityComment
     * @return bool
     */
    public function isEditCommentAllowed(
        CreditmemoCommentInterface|InvoiceCommentInterface|ShipmentCommentInterface $salesEntityComment
    ): bool {
        if (!empty($salesEntityComment->getId())) {
            if ($salesEntityComment->getData('user_id') != $this->userContext->getUserId() ||
                $salesEntityComment->getData('user_type') != $this->userContext->getUserType()) {
                return false;
            }
        }

        return true;
    }
}
