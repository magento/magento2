<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryMassSourceAssignAdminUi\Model\MassAssignSessionStorage;
use Magento\InventoryMassSourceAssignApi\Api\MassAssignInterface;

class MassAssignPost extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var MassAssignSessionStorage
     */
    private $massAssignSessionStorage;

    /**
     * @var MassAssignInterface
     */
    private $massAssign;

    /**
     * @param Action\Context $context
     * @param MassAssignInterface $massAssign
     * @param MassAssignSessionStorage $massAssignSessionStorage
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        MassAssignInterface $massAssign,
        MassAssignSessionStorage $massAssignSessionStorage
    ) {
        parent::__construct($context);

        $this->massAssignSessionStorage = $massAssignSessionStorage;
        $this->massAssign = $massAssign;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sourceCodes = $this->getRequest()->getParam('sources', []);
        $skus = $this->massAssignSessionStorage->getProductsSkus();

        try {
            $count = $this->massAssign->execute($skus, $sourceCodes);
            $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count assignments.', [
                'count' => $count,
            ]));
        } catch (ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
