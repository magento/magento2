<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\ConfirmationLog;

use Magento\Customer\Model\ConfirmationLog as Model;
use Magento\Customer\Model\ResourceModel\ConfirmationLog as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     * phpcs:disable Magento2.CodeAnalysis.ProtectedModifier
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable Magento2.CodeAnalysis.ProtectedModifier
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
