<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @since 2.0.0
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersDeleted = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $this->customerRepository->deleteById($customerId);
            $customersDeleted++;
        }

        if ($customersDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $customersDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
