<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Controller\Store;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use \Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Store\Model\StoreSwitcher\HashGenerator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreIsInactiveException;

/**
 * Builds correct url to target store and performs redirect.
 */
class SwitchRequest extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
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
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @param Context $context
     * @param StoreRepositoryInterface $storeRepository
     * @param CustomerSession $session
     * @param DeploymentConfig $deploymentConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param HashGenerator $hashGenerator
     */
    public function __construct(
        Context $context,
        StoreRepositoryInterface $storeRepository,
        CustomerSession $session,
        DeploymentConfig $deploymentConfig,
        CustomerRepositoryInterface $customerRepository,
        HashGenerator $hashGenerator
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->customerSession = $session;
        $this->deploymentConfig = $deploymentConfig;
        $this->customerRepository = $customerRepository;
        $this->hashGenerator = $hashGenerator;
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
            $fromStore = $this->storeRepository->get($fromStoreCode);
            $targetStore=$this->storeRepository->getActiveStoreByCode($targetStoreCode);
            $targetUrl=$targetStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        } catch (NoSuchEntityException $e) {
            $error = __('Requested store is not found.');
        } catch (StoreIsInactiveException $e) {
            $error = __('Requested store is inactive.');
        }

        if ($this->hashGenerator->validateHash($signature, [$customerId, $timeStamp, $fromStoreCode])) {
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
            //redirect to previous store
            $this->getResponse()->setRedirect($fromStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK));
        } else {
            $this->getResponse()->setRedirect($targetUrl);
        }
    }
}
