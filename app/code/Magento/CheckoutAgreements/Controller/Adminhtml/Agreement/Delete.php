<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends Agreement implements HttpPostActionInterface
{
    /**
     * @var CheckoutAgreementsRepositoryInterface
     */
    private $agreementRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param CheckoutAgreementsRepositoryInterface $agreementRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ?CheckoutAgreementsRepositoryInterface $agreementRepository = null
    ) {
        $this->agreementRepository = $agreementRepository ?:
                ObjectManager::getInstance()->get(CheckoutAgreementsRepositoryInterface::class);
        parent::__construct($context, $coreRegistry);
    }
    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $agreement = $this->agreementRepository->get($id);
        if (!$agreement->getAgreementId()) {
            $this->messageManager->addError(__('This condition no longer exists.'));
            $this->_redirect('checkout/*/');
            return;
        }

        try {
            $this->agreementRepository->delete($agreement);
            $this->messageManager->addSuccess(__('You deleted the condition.'));
            $this->_redirect('checkout/*/');
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong  while deleting this condition.'));
        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
