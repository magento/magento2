<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source;

use Magento\InventoryCatalogAdminUi\Controller\Adminhtml\BulkAbstract;

class BulkUnassign extends BulkAbstract
{
    /**
     * @inheritdoc
     */
    protected function getTitle(): string
    {
        return 'Bulk source unassignment';
    }
}
