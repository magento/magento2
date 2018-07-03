<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;

abstract class Agreement extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CheckoutAgreements::checkoutagreement';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var CheckoutAgreementsRepositoryInterface
     */
    protected $_agreementRepository;
    
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CheckoutAgreementsRepositoryInterface $agreementRepository
     * @param \Magento\CheckoutAgreements\Model\AgreementFactory $agreementFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        CheckoutAgreementsRepositoryInterface $agreementRepository = null,
        \Magento\CheckoutAgreements\Model\AgreementFactory $agreementFactory = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_agreementRepository = $agreementRepository ?:
                ObjectManager::getInstance()->get(CheckoutAgreementsRepositoryInterface::class);
        $this->_agreementFactory = $agreementFactory ?:
                ObjectManager::getInstance()->get(\Magento\CheckoutAgreements\Model\AgreementFactory::class);
        parent::__construct($context);
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_CheckoutAgreements::sales_checkoutagreement'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Checkout Conditions'),
            __('Checkout Terms and Conditions')
        );
        return $this;
    }
}
