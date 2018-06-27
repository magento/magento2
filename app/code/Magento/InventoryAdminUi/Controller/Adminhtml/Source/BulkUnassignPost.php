<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryApi\Api\BulkSourceUnassignInterface;

class BulkUnassignPost extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var BulkSessionProductsStorage
     */
    private $bulkSessionProductsStorage;

    /**
     * @var BulkSourceUnassignInterface
     */
    private $bulkSourceUnassign;

    /**
     * @param Action\Context $context
     * @param BulkSourceUnassignInterface $bulkSourceUnassign
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        BulkSourceUnassignInterface $bulkSourceUnassign,
        BulkSessionProductsStorage $bulkSessionProductsStorage
    ) {
        parent::__construct($context);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkSourceUnassign = $bulkSourceUnassign;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sourceCodes = $this->getRequest()->getParam('sources', []);
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();

        try {
            $count = $this->bulkSourceUnassign->execute($skus, $sourceCodes);
            $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count unassignments.', [
                'count' => $count,
            ]));
        } catch (ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
