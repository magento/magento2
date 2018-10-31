<?php
declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Button for deletion of customer address in admin *
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        Action\Context $context,
        AddressRepositoryInterface $addressRepository
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
    }

    /**
     * Delete customer address action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): Redirect
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('id', false);
        if ($addressId && $this->addressRepository->getById($addressId)->getCustomerId() === $customerId) {
            try {
                $this->addressRepository->deleteById($addressId);
                $this->messageManager->addSuccessMessage(__('You deleted the address.'));
            } catch (\Exception $other) {
                $this->messageManager->addExceptionMessage($other, __('We can\'t delete the address right now.'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('customer/index/edit/id', ['id' => $customerId]);
    }
}
