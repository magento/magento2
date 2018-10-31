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
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

/**
 * Abstract class for customer default addresses changing
 */
abstract class AbstractDefaultAddress extends Action implements HttpPostActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * Execute action to set customer default billing or shipping address
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute(): Redirect
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('id', false);
        if ($addressId) {
            try {
                $address = $this->addressRepository->getById($addressId)->setCustomerId($customerId);
                $this->setAddressAsDefault($address);
                $this->addressRepository->save($address);

                $this->messageManager->addSuccessMessage($this->getSuccessMessage());
            } catch (\Exception $other) {
                $this->logger->critical($other);
                $this->messageManager->addExceptionMessage($other, $this->getExceptionMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('customer/index/edit/id', ['id' => $customerId]);
    }

    /**
     * Set passed address as customer's default address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return $this
     */
    abstract protected function setAddressAsDefault($address);

    /**
     * Get success message about default address changed
     *
     * @return \Magento\Framework\Phrase
     */
    abstract protected function getSuccessMessage(): Phrase;

    /**
     * Get error message about unsuccessful attempt to change default address
     *
     * @return \Magento\Framework\Phrase
     */
    abstract protected function getExceptionMessage(): Phrase;
}
