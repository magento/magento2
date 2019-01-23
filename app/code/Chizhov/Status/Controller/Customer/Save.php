<?php

declare(strict_types=1);

namespace Chizhov\Status\Controller\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

class Save extends Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $validator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Validator $validator,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);

        $this->validator = $validator;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Save customer status action.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http|\Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var \Magento\Framework\Controller\Result\Redirect $redirectResult */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->validator->validate($request) || !$request->isPost()) {
            $this->messageManager->addErrorMessage(__("The account status couldn't be saved."));

            return $redirectResult->setPath('*/*');
        }

        try {
            $customerId = (int)$this->customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $customerStatus = $request->getPostValue('status');

            $customer->getExtensionAttributes()->setChizhovCustomerStatus($customerStatus);
            $this->customerRepository->save($customer);

            $this->messageManager->addSuccessMessage(__('The account status has been saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__("The account status couldn't be saved."));
        }

        return $redirectResult->setPath('*/*');
    }
}
