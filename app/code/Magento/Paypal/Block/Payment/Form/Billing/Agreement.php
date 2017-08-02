<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payment\Form\Billing;

/**
 * Paypal Billing Agreement form block
 * @since 2.0.0
 */
class Agreement extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Paypal::payment/form/billing/agreement.phtml';

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     * @since 2.0.0
     */
    protected $_agreementFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        array $data = []
    ) {
        $this->_agreementFactory = $agreementFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTransportName(
            \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID
        );
    }

    /**
     * Retrieve available customer billing agreements
     *
     * @return array
     * @since 2.0.0
     */
    public function getBillingAgreements()
    {
        $data = [];
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getParentBlock()->getQuote();
        if (!$quote || !$quote->getCustomerId()) {
            return $data;
        }
        $collection = $this->_agreementFactory->create()->getAvailableCustomerBillingAgreements(
            $quote->getCustomerId()
        );

        foreach ($collection as $item) {
            $data[$item->getId()] = $item->getReferenceId();
        }
        return $data;
    }
}
