<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DeploymentConfig as DeploymentConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Process one time token and build redirect url
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class HashProcessor implements StoreSwitcherInterface
{
    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var customerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param HashGenerator $hashGenerator
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param CustomerRepository $customerRepository
     * @param CustomerSession $customerSession
     */
    public function __construct(
        HashGenerator $hashGenerator,
        RequestInterface $request,
        ManagerInterface $messageManager,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession
    ) {
        $this->hashGenerator = $hashGenerator;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Builds redirect url with token
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $customerId = $this->request->getParam('customer_id');

        if ($customerId) {
            $fromStoreCode = (string)$this->request->getParam('___from_store');
            $timeStamp = (string)$this->request->getParam('time_stamp');
            $signature = (string)$this->request->getParam('signature');

            $error = null;

            $data = new HashData(
                [
                    "customer_id" => $customerId,
                    "time_stamp" => $timeStamp,
                    "___from_store" => $fromStoreCode
                ]
            );

            if ($redirectUrl && $this->hashGenerator->validateHash($signature, $data)) {
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
                $error = __('The requested store cannot be found. Please check the request and try again.');
            }

            if ($error !== null) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        return $redirectUrl;
    }
}
