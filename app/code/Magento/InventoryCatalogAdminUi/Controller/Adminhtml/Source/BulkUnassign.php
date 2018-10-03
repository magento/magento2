<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Bulk\BulkPageProcessor;

/**
 * Mass unassign sources from products.
 */
class BulkUnassign extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var BulkPageProcessor
     */
    private $processBulkPage;

    /**
     * @param Action\Context $context
     * @param BulkPageProcessor $processBulkPage
     */
    public function __construct(
        Action\Context $context,
        BulkPageProcessor $processBulkPage
    ) {
        parent::__construct($context);

        $this->processBulkPage = $processBulkPage;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        return $this->processBulkPage->execute(__('Bulk source unassignment'), true);
    }
}
