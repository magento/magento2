<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payment\Method\Billing;

/**
 * Billing Agreement Payment Method Abstract model
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
abstract class AbstractAgreement extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Transport billing agreement id
     */
    const TRANSPORT_BILLING_AGREEMENT_ID = 'ba_agreement_id';

    const PAYMENT_INFO_REFERENCE_ID = 'ba_reference_id';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info\Billing\Agreement';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Payment\Form\Billing\Agreement';

    /**
     * Is method instance available
     *
     * @var null|bool
     */
    protected $_isAvailable = null;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_agreementFactory = $agreementFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Check whether method is available
     *
     * @param \Magento\Paypal\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if ($this->_isAvailable === null) {
            $this->_isAvailable = parent::isAvailable($quote) && $this->_isAvailable($quote);
            $this->_canUseCheckout = $this->_isAvailable && $this->_canUseCheckout;
            $this->_canUseInternal = $this->_isAvailable && $this->_canUseInternal;
        }
        return $this->_isAvailable;
    }

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     * @return \Magento\Payment\Model\Info
     */
    public function assignData($data)
    {
        $result = parent::assignData($data);

        $key = self::TRANSPORT_BILLING_AGREEMENT_ID;
        $id = false;
        if (is_array($data) && isset($data[$key])) {
            $id = $data[$key];
        } elseif ($data instanceof \Magento\Framework\Object && $data->getData($key)) {
            $id = $data->getData($key);
        }
        if ($id) {
            $info = $this->getInfoInstance();
            $ba = $this->_agreementFactory->create()->load($id);
            if ($ba->getId() && $ba->getCustomerId() == $info->getQuote()->getCustomerId()) {
                $info->setAdditionalInformation(
                    $key,
                    $id
                )->setAdditionalInformation(
                    self::PAYMENT_INFO_REFERENCE_ID,
                    $ba->getReferenceId()
                );
            }
        }
        return $result;
    }

    /**
     * @param object $quote
     * @return void
     */
    abstract protected function _isAvailable($quote);
}
