<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\InventoryMassSourceAssignAdminUi\Model\MassAssignSessionStorage;
use Magento\Ui\Component\MassAction\Filter;

class MassAssign extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var MassAssignSessionStorage
     */
    private $massAssignSessionStorage;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Action\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param MassAssignSessionStorage $massAssignSessionStorage
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        MassAssignSessionStorage $massAssignSessionStorage
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->massAssignSessionStorage = $massAssignSessionStorage;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('catalog/product/index');
        }

        $this->massAssignSessionStorage->setProductIds($collection->getAllIds());

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Mass product source assignment'));

        return $resultPage;
    }
}
