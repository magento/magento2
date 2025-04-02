<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ObjectManager;
use Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit as BlockEdit;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Edit extends Agreement implements HttpGetActionInterface
{
    /**
     * @var AgreementFactory
     */
    private $agreementFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AgreementFactory $agreementFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ?AgreementFactory $agreementFactory = null
    ) {
        $this->agreementFactory = $agreementFactory ?:
                ObjectManager::getInstance()->get(AgreementFactory::class);
        parent::__construct($context, $coreRegistry);
    }
    /**
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $agreementModel = $this->agreementFactory->create();

        if ($id) {
            $agreementModel->load($id);
            if (!$agreementModel->getId()) {
                $this->messageManager->addError(__('This condition no longer exists.'));
                $this->_redirect('checkout/*/');
                return;
            }
        }

        $data = $this->_session->getAgreementData(true);
        if (!empty($data)) {
            $agreementModel->setData($data);
        }

        $this->_coreRegistry->register('checkout_agreement', $agreementModel);

        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Condition') : __('New Condition'),
            $id ? __('Edit Condition') : __('New Condition')
        )->_addContent(
            $this->_view->getLayout()->createBlock(
                BlockEdit::class
            )->setData(
                'action',
                $this->getUrl('checkout/*/save')
            )
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Terms and Conditions'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $agreementModel->getId() ? $agreementModel->getName() : __('New Condition')
        );
        $this->_view->renderLayout();
    }
}
