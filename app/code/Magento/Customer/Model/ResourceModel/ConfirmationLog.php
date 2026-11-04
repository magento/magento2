<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConfirmationLog extends AbstractDb
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable Magento2.CodeAnalysis.ProtectedModifier
     */
    protected function _construct()
    {
        $this->_init('customer_confirmation_log', 'id');
    }
}
