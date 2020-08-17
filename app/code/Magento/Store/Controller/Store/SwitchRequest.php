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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreSwitcher\HashGenerator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Url\DecoderInterface;
use \Magento\Framework\App\ActionInterface;
use Magento\Store\Model\StoreSwitcher\HashGenerator\HashData;

/**
 * Builds correct url to target store and performs redirect.
 */
class SwitchRequest extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{

    /**
     * @var customerSession
     */
    private $customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var DecoderInterface
     */
    private $urlDecoder;

    /**
     * @param Context $context
     * @param CustomerSession $session
     * @param CustomerRepositoryInterface $customerRepository
     * @param HashGenerator $hashGenerator
     * @param DecoderInterface $urlDecoder
     */
    public function __construct(
        Context $context,
        CustomerSession $session,
        CustomerRepositoryInterface $customerRepository,
        HashGenerator $hashGenerator,
        DecoderInterface $urlDecoder
    ) {
        parent::__construct($context);
        $this->customerSession = $session;
        $this->customerRepository = $customerRepository;
        $this->hashGenerator = $hashGenerator;
        $this->urlDecoder = $urlDecoder;
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
        $signature = (string)$this->_request->getParam('signature');
        $error = null;
        $encodedUrl = (string)$this->_request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED);
        $targetUrl = $this->urlDecoder->decode($encodedUrl);

        $data = new HashData(
            [
                "customer_id" => $customerId,
                "time_stamp" => $timeStamp,
                "___from_store" => $fromStoreCode
            ]
        );

        if ($targetUrl && $this->hashGenerator->validateHash($signature, $data)) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                if (!$this->customerSession->isLoggedIn()) {
                    $this->customerSession->setCustomerDataAsLoggedIn($customer);
                }
                $this->getResponse()->setRedirect($targetUrl);
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
}
