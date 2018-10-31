<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Backend\Model\View\Result\Redirect;

/**
 * Class to delete selected customer addresses through massaction
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see MassDelete::_isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->addressRepository = $addressRepository;
        parent::__construct($context);
    }

    /**
     * Delete specified customer addresses using grid massaction
     *
     * @return Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute(): Redirect
    {
        /** @var \Magento\Customer\Model\ResourceModel\Address\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        // Get id of the first item from addresses collection for the ResultRedirect and build a correct redirect URL
        $customerId = $collection->getFirstItem()->getParentId();

        /** @var \Magento\Customer\Model\Address $address */
        foreach ($collection as $address) {
            $this->addressRepository->deleteById($address->getId());
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('customer/index/edit/id', ['id' => $customerId]);
    }
}
