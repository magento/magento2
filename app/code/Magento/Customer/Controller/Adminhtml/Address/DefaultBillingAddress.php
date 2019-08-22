<?php
declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class to process set default billing address action
 */
class DefaultBillingAddress extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action to set customer default billing address
     *
     * @return Json
     */
    public function execute(): Json
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('id', false);
        $error = true;
        $message = __('There is no address id in setting default billing address.');

        if ($addressId) {
            try {
                $address = $this->addressRepository->getById($addressId)->setCustomerId($customerId);
                $this->setAddressAsDefault($address);
                $this->addressRepository->save($address);
                $message = __('Default billing address has been changed.');
                $error = false;
            } catch (\Exception $e) {
                $message = __('We can\'t change default billing address right now.');
                $this->logger->critical($e);
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }

    /**
     * Set address as default billing address
     *
     * @param AddressInterface $address
     * @return void
     */
    private function setAddressAsDefault(AddressInterface $address): void
    {
        $address->setIsDefaultBilling(true);
    }
}
