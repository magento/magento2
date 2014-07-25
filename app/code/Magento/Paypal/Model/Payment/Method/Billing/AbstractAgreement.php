<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\Payment\Method\Billing;

/**
 * Billing Agreement Payment Method Abstract model
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
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        array $data = array()
    ) {
        $this->_agreementFactory = $agreementFactory;
        parent::__construct($eventManager, $paymentData, $scopeConfig, $logAdapterFactory, $data);
    }

    /**
     * Check whether method is available
     *
     * @param \Magento\Paypal\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (is_null($this->_isAvailable)) {
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
