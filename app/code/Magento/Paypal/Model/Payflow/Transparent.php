<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Paypal\Model\Payflow\Service\Response\Handler\HandlerInterface;
use Magento\Paypal\Model\Payflow\Service\Response\Validator\ResponseValidator;
use Magento\Paypal\Model\Payflowpro;

/**
 * Payflow Pro payment gateway model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transparent extends Payflowpro implements TransparentInterface
{
    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Transparent\Info';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payflow\Info';

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ConfigInterfaceFactory $configFactory
     * @param Gateway $gateway
     * @param HandlerInterface $errorHandler
     * @param ResponseValidator $responseValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
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
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ConfigInterfaceFactory $configFactory,
        Gateway $gateway,
        HandlerInterface $errorHandler,
        ResponseValidator $responseValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $storeManager,
            $configFactory,
            $gateway,
            $errorHandler,
            $resource,
            $resourceCollection,
            $data
        );
        $this->responseValidator = $responseValidator;
    }


    /**
     * Do not validate payment form using server methods
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Performs authorize transaction
     *
     * @param InfoInterface|Object $payment
     * @param float $amount
     * @return $this
     * @throws InvalidTransitionException
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        /** @var DataObject $request */
        $request = $this->buildBasicRequest();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $this->addRequestOrderInfo($request, $order);
        $request = $this->fillCustomerContacts($order, $request);

        $token = $payment->getAdditionalInformation('pnref');
        $request->setData('trxtype', self::TRXTYPE_AUTH_ONLY);
        $request->setData('origid', $token);
        $request->setData('amt', round($amount, 2));
        $request->setData('currency', $order->getBaseCurrencyCode());

        $response = $this->postRequest($request, $this->getConfig());
        $this->processErrors($response);

        try {
            $this->responseValidator->validate($response);
        } catch (LocalizedException $exception) {
            $payment->setParentTransactionId($response->getPnref());
            $this->void($payment);
            throw new LocalizedException(__('Error processing payment, please try again later.'));
        }

        $this->setTransStatus($payment, $response);

        $payment->unsAdditionalInformation('pnref');

        return $this;
    }

    /**
     * {inheritdoc}
     */
    public function getConfigInterface()
    {
        return parent::getConfig();
    }
}
