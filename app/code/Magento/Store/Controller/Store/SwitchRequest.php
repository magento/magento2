<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Builds correct url to target store and performs redirect.
 */
class SwitchRequest extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var customerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CustomerSession $session
     * @param DeploymentConfig $deploymentConfig
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CustomerSession $session,
        DeploymentConfig $deploymentConfig,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->customerSession = $session;
        $this->deploymentConfig = $deploymentConfig;
        $this->customerRepository=$customerRepository;
    }

    /**
     * Execute action
     *
     * @return void
     */
    public function execute()
    {
        $fromStoreCode = (string)$this->_request->getParam('___from_store');
        $customerId = (int)$this->_request->getParam('customer_id');
        $timeStamp = (string)$this->_request->getParam('time_stamp');
        $targetStoreCode = $this->_request->getParam('___to_store');
        $signature = (string)$this->_request->getParam('signature');
        $error = null;

        try {
            /** @var \Magento\Store\Model\Store $fromStore */
            $fromStore = $this->storeRepository->get($fromStoreCode);
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found.');
        }

        if ($this->validateHash($customerId, $timeStamp, $signature, $fromStoreCode)) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                if (!$this->customerSession->isLoggedIn()) {
                    $this->customerSession->setCustomerDataAsLoggedIn($customer);
                }
            } catch (NoSuchEntityException $e) {
                $error = __('The requested customer does not exist.');
            } catch (LocalizedException $e) {
                $error = __('There was an error retrieving the customer record.');
            }
        } else {
            $error = __('Invalid request. Store switching action cannot be performed at this time.');
        }

        if ($error !== null) {
            $this->messageManager->addErrorMessage($error);
            $this->getResponse()->setRedirect('/');
        } else {
            $this->getResponse()->setRedirect("/$targetStoreCode");
        }
    }

    /**
     * Validates one time token
     *
     * @param int $customerId
     * @param string $timeStamp
     * @param string $signature
     * @param string $fromStoreCode
     * @return bool
     */
    private function validateHash(int $customerId, string $timeStamp, string $signature, string $fromStoreCode): bool
    {

        if ($customerId && $timeStamp && $signature) {
            $data = implode(',', [$customerId, $timeStamp, $fromStoreCode]);
            $key = (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
            if (time() - $timeStamp <= 5 && hash_equals($signature, hash_hmac('sha256', $data, $key))) {
                return true;
            }
        }
        return false;
    }
}