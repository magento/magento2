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
namespace Magento\Authorizenet\Model;

class Authorizenet extends \Magento\Payment\Model\Method\Cc
{
    /*
     * AIM gateway url
     */
    const CGI_URL = 'https://secure.authorize.net/gateway/transact.dll';

    /*
     * Transaction Details gateway url
     */
    const CGI_URL_TD = 'https://apitest.authorize.net/xml/v1/request.api';

    const REQUEST_METHOD_CC = 'CC';

    const REQUEST_METHOD_ECHECK = 'ECHECK';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';

    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';

    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';

    const REQUEST_TYPE_CREDIT = 'CREDIT';

    const REQUEST_TYPE_VOID = 'VOID';

    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const ECHECK_ACCT_TYPE_CHECKING = 'CHECKING';

    const ECHECK_ACCT_TYPE_BUSINESS = 'BUSINESSCHECKING';

    const ECHECK_ACCT_TYPE_SAVINGS = 'SAVINGS';

    const ECHECK_TRANS_TYPE_CCD = 'CCD';

    const ECHECK_TRANS_TYPE_PPD = 'PPD';

    const ECHECK_TRANS_TYPE_TEL = 'TEL';

    const ECHECK_TRANS_TYPE_WEB = 'WEB';

    const RESPONSE_DELIM_CHAR = '(~)';

    const RESPONSE_CODE_APPROVED = 1;

    const RESPONSE_CODE_DECLINED = 2;

    const RESPONSE_CODE_ERROR = 3;

    const RESPONSE_CODE_HELD = 4;

    const RESPONSE_REASON_CODE_APPROVED = 1;

    const RESPONSE_REASON_CODE_NOT_FOUND = 16;

    const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;

    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;

    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;

    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    const PARTIAL_AUTH_CARDS_LIMIT = 5;

    const PARTIAL_AUTH_LAST_SUCCESS = 'last_success';

    const PARTIAL_AUTH_LAST_DECLINED = 'last_declined';

    const PARTIAL_AUTH_ALL_CANCELED = 'all_canceled';

    const PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED = 'card_limit_exceeded';

    const PARTIAL_AUTH_DATA_CHANGED = 'data_changed';

    const METHOD_CODE = 'authorizenet';

    const TRANSACTION_STATUS_EXPIRED = 'expired';

    /**
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    /**
     * Form block type
     *
     * @var string
     */
    protected $_formBlockType = 'Magento\Authorizenet\Block\Authorizenet\Form\Cc';

    /**
     * Info block type
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Authorizenet\Block\Authorizenet\Info\Cc';

    /**#@+
     * Availability options
     *
     * @var bool
     */
    protected $_isGateway = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canCapturePartial = false;

    protected $_canRefund = true;

    protected $_canRefundInvoicePartial = true;

    protected $_canVoid = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = true;

    protected $_canSaveCc = false;

    protected $_canFetchTransactionInfo = true;

    /**#@-*/

    /**
     * @var string[]
     */
    protected $_allowCurrencyCode = array('USD');

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = array(
        'x_login',
        'x_tran_key',
        'x_card_num',
        'x_exp_date',
        'x_card_code',
        'x_bank_aba_code',
        'x_bank_name',
        'x_bank_acct_num',
        'x_bank_acct_type',
        'x_bank_acct_name',
        'x_echeck_type'
    );

    /**
     * Key for storing fraud transaction flag in additional information of payment model
     * @var string
     */
    protected $_isTransactionFraud = 'is_transaction_fraud';

    /**
     * Key for storing transaction id in additional information of payment model
     * @var string
     */
    protected $_realTransactionIdKey = 'real_transaction_id';

    /**
     * Key for storing split tender id in additional information of payment model
     * @var string
     */
    protected $_splitTenderIdKey = 'split_tender_id';

    /**
     * Key for storing locking gateway actions flag in additional information of payment model
     * @var string
     */
    protected $_isGatewayActionsLockedKey = 'is_gateway_actions_locked';

    /**
     * Key for storing partial authorization last action state in session
     * @var string
     */
    protected $_partialAuthorizationLastActionStateSessionKey = 'authorizenet_last_action_state';

    /**
     * Key for storing partial authorization checksum in session
     * @var string
     */
    protected $_partialAuthorizationChecksumSessionKey = 'authorizenet_checksum';

    /**
     * Fields for creating place request checksum
     *
     * @var string[]
     */
    protected $_partialAuthorizationChecksumDataKeys = array(
        'x_version',
        'x_test_request',
        'x_login',
        'x_test_request',
        'x_allow_partial_auth',
        'x_amount',
        'x_currency_code',
        'x_type',
        'x_first_name',
        'x_last_name',
        'x_company',
        'x_address',
        'x_city',
        'x_state',
        'x_zip',
        'x_country',
        'x_phone',
        'x_fax',
        'x_cust_id',
        'x_customer_ip',
        'x_customer_tax_id',
        'x_email',
        'x_email_customer',
        'x_merchant_email',
        'x_ship_to_first_name',
        'x_ship_to_last_name',
        'x_ship_to_company',
        'x_ship_to_address',
        'x_ship_to_city',
        'x_ship_to_state',
        'x_ship_to_zip',
        'x_ship_to_country',
        'x_po_num',
        'x_tax',
        'x_freight'
    );

    /**
     * Centinel cardinal fields map
     *
     * @var string[]
     */
    protected $_centinelFieldMap = array(
        'centinel_cavv' => 'x_cardholder_authentication_value',
        'centinel_eci' => 'x_authentication_indicator'
    );

    /**
     * @var \Magento\Authorizenet\Model\Authorizenet\Cards
     */
    protected $_cardsStorage = null;

    /**
     * Authorize.net data
     *
     * @var \Magento\Authorizenet\Helper\Data
     */
    protected $_authorizenetData = null;

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * Order factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * Result factory
     *
     * @var \Magento\Authorizenet\Model\Authorizenet\ResultFactory
     */
    protected $_resultFactory;

    /**
     * Request factory
     *
     * @var \Magento\Authorizenet\Model\Authorizenet\RequestFactory
     */
    protected $_requestFactory;

    /**
     * Cards factory
     *
     * @var \Magento\Authorizenet\Model\Authorizenet\CardsFactory
     */
    protected $_cardsFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param \Magento\Authorizenet\Model\Authorizenet\CardsFactory $cardsFactory
     * @param \Magento\Authorizenet\Model\Authorizenet\RequestFactory $requestFactory
     * @param \Magento\Authorizenet\Model\Authorizenet\ResultFactory $resultFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Authorizenet\Helper\Data $authorizenetData
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Centinel\Model\Service $centinelService,
        \Magento\Authorizenet\Model\Authorizenet\CardsFactory $cardsFactory,
        \Magento\Authorizenet\Model\Authorizenet\RequestFactory $requestFactory,
        \Magento\Authorizenet\Model\Authorizenet\ResultFactory $resultFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Authorizenet\Helper\Data $authorizenetData,
        array $data = array()
    ) {
        $this->_cardsFactory = $cardsFactory;
        $this->_requestFactory = $requestFactory;
        $this->_resultFactory = $resultFactory;
        $this->_orderFactory = $orderFactory;
        $this->_session = $session;
        $this->_authorizenetData = $authorizenetData;
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $logger,
            $moduleList,
            $localeDate,
            $centinelService,
            $data
        );
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->getAcceptedCurrencyCodes())) {
            return false;
        }
        return true;
    }

    /**
     * Return array of currency codes supplied by Payment Gateway
     *
     * @return array
     */
    public function getAcceptedCurrencyCodes()
    {
        if (!$this->hasData('_accepted_currency')) {
            $acceptedCurrencyCodes = $this->_allowCurrencyCode;
            $acceptedCurrencyCodes[] = $this->getConfigData('currency');
            $this->setData('_accepted_currency', $acceptedCurrencyCodes);
        }
        return $this->_getData('_accepted_currency');
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if ($this->_isGatewayActionsLocked($this->getInfoInstance())) {
            return false;
        }
        if ($this->_isPreauthorizeCapture($this->getInfoInstance())) {
            return true;
        }

        /**
         * If there are not transactions it is placing order and capturing is available
         */
        foreach ($this->getCardsStorage()->getCards() as $card) {
            $lastTransaction = $this->getInfoInstance()->getTransaction($card->getLastTransId());
            if ($lastTransaction) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        if ($this->_isGatewayActionsLocked($this->getInfoInstance()) || $this->getCardsStorage()->getCardsCount() <= 0
        ) {
            return false;
        }
        foreach ($this->getCardsStorage()->getCards() as $card) {
            $lastTransaction = $this->getInfoInstance()->getTransaction($card->getLastTransId());
            if ($lastTransaction &&
                $lastTransaction->getTxnType() == \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE &&
                !$lastTransaction->getIsClosed()
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Framework\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Framework\Object $payment)
    {
        if ($this->_isGatewayActionsLocked($this->getInfoInstance())) {
            return false;
        }
        return $this->_isPreauthorizeCapture($this->getInfoInstance());
    }

    /**
     * Set partial authorization last action state into session
     *
     * @param string $state
     * @return $this
     */
    public function setPartialAuthorizationLastActionState($state)
    {
        $this->_session->setData($this->_partialAuthorizationLastActionStateSessionKey, $state);
        return $this;
    }

    /**
     * Return partial authorization last action state from session
     *
     * @return string
     */
    public function getPartialAuthorizationLastActionState()
    {
        return $this->_session->getData($this->_partialAuthorizationLastActionStateSessionKey);
    }

    /**
     * Unset partial authorization last action state in session
     *
     * @return $this
     */
    public function unsetPartialAuthorizationLastActionState()
    {
        $this->_session->setData($this->_partialAuthorizationLastActionStateSessionKey, false);
        return $this;
    }

    /**
     * Send authorize request to gateway
     *
     * @param  \Magento\Payment\Model\Info $payment
     * @param  float $amount
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function authorize(\Magento\Framework\Object $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid amount for authorization.'));
        }

        $this->_initCardsStorage($payment);

        if ($this->isPartialAuthorization($payment)) {
            $this->_partialAuthorization($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
            $payment->setSkipTransactionCreation(true);
            return $this;
        }

        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Send capture request to gateway
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function capture(\Magento\Framework\Object $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid amount for capture.'));
        }
        $this->_initCardsStorage($payment);
        if ($this->_isPreauthorizeCapture($payment)) {
            $this->_preauthorizeCapture($payment, $amount);
        } else if ($this->isPartialAuthorization($payment)) {
            $this->_partialAuthorization($payment, $amount, self::REQUEST_TYPE_AUTH_CAPTURE);
        } else {
            $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_CAPTURE);
        }
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Void the payment through gateway
     *
     * @param  \Magento\Payment\Model\Info $payment
     * @return $this
     */
    public function void(\Magento\Framework\Object $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);

        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            try {
                $newTransaction = $this->_voidCardTransaction($payment, $card);
                $messages[] = $newTransaction->getMessage();
                $isSuccessful = true;
            } catch (\Exception $e) {
                $messages[] = $e->getMessage();
                $isFiled = true;
                continue;
            }
            $cardsStorage->updateCard($card);
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Cancel the payment through gateway
     *
     * @param  \Magento\Payment\Model\Info $payment
     * @return $this
     */
    public function cancel(\Magento\Framework\Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * Refund the amount with transaction id
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $requestedAmount
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function refund(\Magento\Framework\Object $payment, $requestedAmount)
    {
        $cardsStorage = $this->getCardsStorage($payment);

        if ($this->_formatAmount(
            $cardsStorage->getCapturedAmount() - $cardsStorage->getRefundedAmount()
        ) < $requestedAmount
        ) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid amount for refund.'));
        }

        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            if ($requestedAmount > 0) {
                $cardAmountForRefund = $this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount());
                if ($cardAmountForRefund <= 0) {
                    continue;
                }
                if ($cardAmountForRefund > $requestedAmount) {
                    $cardAmountForRefund = $requestedAmount;
                }
                try {
                    $newTransaction = $this->_refundCardTransaction($payment, $cardAmountForRefund, $card);
                    $messages[] = $newTransaction->getMessage();
                    $isSuccessful = true;
                } catch (\Exception $e) {
                    $messages[] = $e->getMessage();
                    $isFiled = true;
                    continue;
                }
                $card->setRefundedAmount($this->_formatAmount($card->getRefundedAmount() + $cardAmountForRefund));
                $cardsStorage->updateCard($card);
                $requestedAmount = $this->_formatAmount($requestedAmount - $cardAmountForRefund);
            } else {
                $payment->setSkipTransactionCreation(true);
                return $this;
            }
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Cancel partial authorizations and flush current split_tender_id record
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function cancelPartialAuthorization(\Magento\Payment\Model\Info $payment)
    {
        if (!$payment->getAdditionalInformation($this->_splitTenderIdKey)) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid split tenderId ID.'));
        }

        $request = $this->_getRequest();
        $request->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));

        $request->setXType(self::REQUEST_TYPE_VOID)->setXMethod(self::REQUEST_METHOD_CC);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $payment->setAdditionalInformation($this->_splitTenderIdKey, null);
                $this->_session->setData($this->_partialAuthorizationChecksumSessionKey, null);
                $this->getCardsStorage($payment)->flushCards();
                $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_ALL_CANCELED);
                return;
            default:
                throw new \Magento\Framework\Model\Exception(__('Something went wrong while canceling the payment.'));
        }
    }

    /**
     * Send request with new payment to gateway
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @param string $requestType
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _place($payment, $amount, $requestType)
    {
        $payment->setAnetTransType($requestType);
        $payment->setAmount($amount);
        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_ONLY:
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                $defaultExceptionMessage = __('Something went wrong while authorizing the payment.');
                break;
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                $defaultExceptionMessage = __('Something went wrong while capturing the payment.');
                break;
        }

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                $this->getCardsStorage($payment)->flushCards();
                $card = $this->_registerCard($result, $payment);
                $this->_addTransaction(
                    $payment,
                    $card->getLastTransId(),
                    $newTransactionType,
                    array('is_transaction_closed' => 0),
                    array($this->_realTransactionIdKey => $card->getLastTransId()),
                    $this->_authorizenetData->getTransactionMessage(
                        $payment,
                        $requestType,
                        $card->getLastTransId(),
                        $card,
                        $amount
                    )
                );
                if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                    $card->setCapturedAmount($card->getProcessedAmount());
                    $this->getCardsStorage($payment)->updateCard($card);
                }
                return $this;
            case self::RESPONSE_CODE_HELD:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED ||
                    $result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW
                ) {
                    $card = $this->_registerCard($result, $payment);
                    $this->_addTransaction(
                        $payment,
                        $card->getLastTransId(),
                        $newTransactionType,
                        array('is_transaction_closed' => 0),
                        array(
                            $this->_realTransactionIdKey => $card->getLastTransId(),
                            $this->_isTransactionFraud => true
                        ),
                        $this->_authorizenetData->getTransactionMessage(
                            $payment,
                            $requestType,
                            $card->getLastTransId(),
                            $card,
                            $amount
                        )
                    );
                    if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                        $card->setCapturedAmount($card->getProcessedAmount());
                        $this->getCardsStorage()->updateCard($card);
                    }
                    $payment->setIsTransactionPending(true)->setIsFraudDetected(true);
                    return $this;
                }
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                    $checksum = $this->_generateChecksum($request, $this->_partialAuthorizationChecksumDataKeys);
                    $this->_session->setData($this->_partialAuthorizationChecksumSessionKey, $checksum);
                    if ($this->_processPartialAuthorizationResponse($result, $payment)) {
                        return $this;
                    }
                }
                throw new \Magento\Framework\Model\Exception($defaultExceptionMessage);
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                throw new \Magento\Framework\Model\Exception($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                throw new \Magento\Framework\Model\Exception($defaultExceptionMessage);
        }
        return $this;
    }

    /**
     * Send request with new payment to gateway during partial authorization process
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @param string $requestType
     * @return $this
     * @throws \Magento\Payment\Model\Info\Exception
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _partialAuthorization($payment, $amount, $requestType)
    {
        $payment->setAnetTransType($requestType);

        /*
         * Try to build checksum of first request and compare with current checksum
         */
        if ($this->getConfigData('partial_authorization_checksum_checking')) {
            $payment->setAmount($amount);
            $firstPlacingRequest = $this->_buildRequest($payment);
            $newChecksum = $this->_generateChecksum(
                $firstPlacingRequest,
                $this->_partialAuthorizationChecksumDataKeys
            );
            $previosChecksum = $this->_session->getData($this->_partialAuthorizationChecksumSessionKey);
            if ($newChecksum != $previosChecksum) {
                $quotePayment = $payment->getOrder()->getQuote()->getPayment();
                $this->cancelPartialAuthorization($payment);
                $this->_clearAssignedData($quotePayment);
                $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_DATA_CHANGED);
                $quotePayment->setAdditionalInformation($payment->getAdditionalInformation());
                throw new \Magento\Payment\Model\Info\Exception(
                    __('The shopping cart contents and/or address has been changed.')
                );
            }
        }

        $amount = $amount - $this->getCardsStorage()->getProcessedAmount();
        if ($amount <= 0) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid amount for partial authorization.'));
        }
        $payment->setAmount($amount);
        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);
        $this->_processPartialAuthorizationResponse($result, $payment);

        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_ONLY:
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                break;
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                break;
        }

        foreach ($this->getCardsStorage()->getCards() as $card) {
            $this->_addTransaction(
                $payment,
                $card->getLastTransId(),
                $newTransactionType,
                array('is_transaction_closed' => 0),
                array($this->_realTransactionIdKey => $card->getLastTransId()),
                $this->_authorizenetData->getTransactionMessage(
                    $payment,
                    $requestType,
                    $card->getLastTransId(),
                    $card,
                    $card->getProcessedAmount()
                )
            );
            if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                $card->setCapturedAmount($card->getProcessedAmount());
                $this->getCardsStorage()->updateCard($card);
            }
        }
        $this->_session->setData($this->_partialAuthorizationChecksumSessionKey, null);
        return $this;
    }

    /**
     * Return true if there are authorized transactions
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    protected function _isPreauthorizeCapture($payment)
    {
        if ($this->getCardsStorage()->getCardsCount() <= 0) {
            return false;
        }
        foreach ($this->getCardsStorage()->getCards() as $card) {
            $lastTransaction = $payment->getTransaction($card->getLastTransId());
            if (!$lastTransaction ||
                $lastTransaction->getTxnType() != \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Send capture request to gateway for capture authorized transactions
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $requestedAmount
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _preauthorizeCapture($payment, $requestedAmount)
    {
        $cardsStorage = $this->getCardsStorage($payment);

        if ($this->_formatAmount(
            $cardsStorage->getProcessedAmount() - $cardsStorage->getCapturedAmount()
        ) < $requestedAmount
        ) {
            throw new \Magento\Framework\Model\Exception(__('This is an invalid amount for capture.'));
        }

        $messages = array();
        $isSuccessful = false;
        $isFiled = false;
        foreach ($cardsStorage->getCards() as $card) {
            if ($requestedAmount > 0) {
                $cardAmountForCapture = $card->getProcessedAmount();
                if ($cardAmountForCapture > $requestedAmount) {
                    $cardAmountForCapture = $requestedAmount;
                }
                try {
                    $newTransaction = $this->_preauthorizeCaptureCardTransaction(
                        $payment,
                        $cardAmountForCapture,
                        $card
                    );
                    $messages[] = $newTransaction->getMessage();
                    $isSuccessful = true;
                } catch (\Exception $e) {
                    $messages[] = $e->getMessage();
                    $isFiled = true;
                    continue;
                }
                $card->setCapturedAmount($cardAmountForCapture);
                $cardsStorage->updateCard($card);
                $requestedAmount = $this->_formatAmount($requestedAmount - $cardAmountForCapture);
            }
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }
        return $this;
    }

    /**
     * Send capture request to gateway for capture authorized transactions of card
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @param \Magento\Framework\Object $card
     * @return \Magento\Sales\Model\Order\Payment\Transaction
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _preauthorizeCaptureCardTransaction($payment, $amount, $card)
    {
        $authTransactionId = $card->getLastTransId();
        $authTransaction = $payment->getTransaction($authTransactionId);
        $realAuthTransactionId = $authTransaction->getAdditionalInformation($this->_realTransactionIdKey);

        $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
        $payment->setXTransId($realAuthTransactionId);
        $payment->setAmount($amount);

        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    $captureTransactionId = $result->getTransactionId() . '-capture';
                    $card->setLastTransId($captureTransactionId);
                    return $this->_addTransaction(
                        $payment,
                        $captureTransactionId,
                        \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
                        array('is_transaction_closed' => 0, 'parent_transaction_id' => $authTransactionId),
                        array($this->_realTransactionIdKey => $result->getTransactionId()),
                        $this->_authorizenetData->getTransactionMessage(
                            $payment,
                            self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE,
                            $result->getTransactionId(),
                            $card,
                            $amount
                        )
                    );
                }
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            case self::RESPONSE_CODE_HELD:
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            default:
                $exceptionMessage = __('Something went wrong while capturing the payment.');
                break;
        }

        $exceptionMessage = $this->_authorizenetData->getTransactionMessage(
            $payment,
            self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE,
            $realAuthTransactionId,
            $card,
            $amount,
            $exceptionMessage
        );
        throw new \Magento\Framework\Model\Exception($exceptionMessage);
    }

    /**
     * Void the card transaction through gateway
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param \Magento\Framework\Object $card
     * @return \Magento\Sales\Model\Order\Payment\Transaction
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _voidCardTransaction($payment, $card)
    {
        $authTransactionId = $card->getLastTransId();
        $authTransaction = $payment->getTransaction($authTransactionId);
        $realAuthTransactionId = $authTransaction->getAdditionalInformation($this->_realTransactionIdKey);

        $payment->setAnetTransType(self::REQUEST_TYPE_VOID);
        $payment->setXTransId($realAuthTransactionId);

        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    $voidTransactionId = $result->getTransactionId() . '-void';
                    $card->setLastTransId($voidTransactionId);
                    return $this->_addTransaction(
                        $payment,
                        $voidTransactionId,
                        \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
                        array(
                            'is_transaction_closed' => 1,
                            'should_close_parent_transaction' => 1,
                            'parent_transaction_id' => $authTransactionId
                        ),
                        array($this->_realTransactionIdKey => $result->getTransactionId()),
                        $this->_authorizenetData->getTransactionMessage(
                            $payment,
                            self::REQUEST_TYPE_VOID,
                            $result->getTransactionId(),
                            $card
                        )
                    );
                }
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_NOT_FOUND &&
                    $this->_isTransactionExpired(
                        $realAuthTransactionId
                    )
                ) {
                    $voidTransactionId = $realAuthTransactionId . '-void';
                    return $this->_addTransaction(
                        $payment,
                        $voidTransactionId,
                        \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
                        array(
                            'is_transaction_closed' => 1,
                            'should_close_parent_transaction' => 1,
                            'parent_transaction_id' => $authTransactionId
                        ),
                        array(),
                        $this->_authorizenetData->getTransactionMessage(
                            $payment,
                            self::REQUEST_TYPE_VOID,
                            null,
                            $card,
                            false,
                            false,
                            __('Parent Authorize.Net transaction (ID %1) expired.', $realAuthTransactionId)
                        )
                    );
                }
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            default:
                $exceptionMessage = __('Something went wrong while voiding the payment.');
                break;
        }

        $exceptionMessage = $this->_authorizenetData->getTransactionMessage(
            $payment,
            self::REQUEST_TYPE_VOID,
            $realAuthTransactionId,
            $card,
            false,
            $exceptionMessage
        );
        throw new \Magento\Framework\Model\Exception($exceptionMessage);
    }

    /**
     * Check if transaction is expired
     *
     * @param  string $realAuthTransactionId
     * @return bool
     */
    protected function _isTransactionExpired($realAuthTransactionId)
    {
        $transactionDetails = $this->_getTransactionDetails($realAuthTransactionId);
        return $transactionDetails->getTransactionStatus() == self::TRANSACTION_STATUS_EXPIRED;
    }

    /**
     * Refund the card transaction through gateway
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @param \Magento\Framework\Object $card
     * @return \Magento\Sales\Model\Order\Payment\Transaction
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _refundCardTransaction($payment, $amount, $card)
    {
        /**
         * Card has last transaction with type "refund" when all captured amount is refunded.
         * Until this moment card has last transaction with type "capture".
         */
        $captureTransactionId = $card->getLastTransId();
        $captureTransaction = $payment->getTransaction($captureTransactionId);
        $realCaptureTransactionId = $captureTransaction->getAdditionalInformation($this->_realTransactionIdKey);

        $payment->setAnetTransType(self::REQUEST_TYPE_CREDIT);
        $payment->setXTransId($realCaptureTransactionId);
        $payment->setAmount($amount);

        $request = $this->_buildRequest($payment);
        $request->setXCardNum($card->getCcLast4());
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                    $refundTransactionId = $result->getTransactionId() . '-refund';
                    $shouldCloseCaptureTransaction = 0;
                    /**
                     * If it is last amount for refund, transaction with type "capture" will be closed
                     * and card will has last transaction with type "refund"
                     */
                    if ($this->_formatAmount($card->getCapturedAmount() - $card->getRefundedAmount()) == $amount) {
                        $card->setLastTransId($refundTransactionId);
                        $shouldCloseCaptureTransaction = 1;
                    }
                    return $this->_addTransaction(
                        $payment,
                        $refundTransactionId,
                        \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND,
                        array(
                            'is_transaction_closed' => 1,
                            'should_close_parent_transaction' => $shouldCloseCaptureTransaction,
                            'parent_transaction_id' => $captureTransactionId
                        ),
                        array($this->_realTransactionIdKey => $result->getTransactionId()),
                        $this->_authorizenetData->getTransactionMessage(
                            $payment,
                            self::REQUEST_TYPE_CREDIT,
                            $result->getTransactionId(),
                            $card,
                            $amount
                        )
                    );
                }
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                $exceptionMessage = $this->_wrapGatewayError($result->getResponseReasonText());
                break;
            default:
                $exceptionMessage = __('Something went wrong while refunding the payment.');
                break;
        }

        $exceptionMessage = $this->_authorizenetData->getTransactionMessage(
            $payment,
            self::REQUEST_TYPE_CREDIT,
            $realCaptureTransactionId,
            $card,
            $amount,
            $exceptionMessage
        );
        throw new \Magento\Framework\Model\Exception($exceptionMessage);
    }

    /**
     * Init cards storage model
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return void
     */
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = $this->_cardsFactory->create()->setPayment($payment);
    }

    /**
     * Return cards storage model
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return \Magento\Authorizenet\Model\Authorizenet\Cards
     */
    public function getCardsStorage($payment = null)
    {
        if (is_null($payment)) {
            $payment = $this->getInfoInstance();
        }
        if (is_null($this->_cardsStorage)) {
            $this->_initCardsStorage($payment);
        }
        return $this->_cardsStorage;
    }

    /**
     * If partial authorization is started method will return true
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return bool
     */
    public function isPartialAuthorization($payment = null)
    {
        if (is_null($payment)) {
            $payment = $this->getInfoInstance();
        }
        return $payment->getAdditionalInformation($this->_splitTenderIdKey);
    }

    /**
     * Mock capture transaction id in invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId(1);
        return $this;
    }

    /**
     * Set transaction ID into creditmemo for informational purposes
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return $this
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        $creditmemo->setTransactionId(1);
        return $this;
    }

    /**
     * Fetch transaction details info
     *
     * Update transaction info if there is one placing transaction only
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        if ($cardsStorage->getCardsCount() != 1) {
            return parent::fetchTransactionInfo($payment, $transactionId);
        }
        $cards = $cardsStorage->getCards();
        $card = array_shift($cards);
        $transactionId = $card->getLastTransId();
        $transaction = $payment->getTransaction($transactionId);

        if (!$transaction->getAdditionalInformation($this->_isTransactionFraud)) {
            return parent::fetchTransactionInfo($payment, $transactionId);
        }

        $response = $this->_getTransactionDetails($transactionId);
        if ($response->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
            $transaction->setAdditionalInformation($this->_isTransactionFraud, false);
            $payment->setIsTransactionApproved(true);
        } elseif ($response->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED) {
            $payment->setIsTransactionDenied(true);
        }
        return parent::fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Set split_tender_id to quote payment if needed
     *
     * @param \Magento\Framework\Object $response
     * @param float $orderPayment
     * @throws \Magento\Payment\Model\Info\Exception
     * @return bool
     */
    protected function _processPartialAuthorizationResponse($response, $orderPayment)
    {
        if (!$response->getSplitTenderId()) {
            return false;
        }

        $quotePayment = $orderPayment->getOrder()->getQuote()->getPayment();
        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
        $exceptionMessage = null;

        try {
            switch ($response->getResponseCode()) {
                case self::RESPONSE_CODE_APPROVED:
                    $this->_registerCard($response, $orderPayment);
                    $this->_clearAssignedData($quotePayment);
                    $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                    return true;
                case self::RESPONSE_CODE_HELD:
                    if ($response->getResponseReasonCode() != self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                        return false;
                    }
                    if ($this->getCardsStorage($orderPayment)->getCardsCount() + 1 >= self::PARTIAL_AUTH_CARDS_LIMIT) {
                        $this->cancelPartialAuthorization($orderPayment);
                        $this->_clearAssignedData($quotePayment);
                        $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_CARDS_LIMIT_EXCEEDED);
                        $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                        $exceptionMessage = __(
                            'You have reached the maximum number of credit cards ' .
                            'allowed to be used for the payment.'
                        );
                        break;
                    }
                    $orderPayment->setAdditionalInformation($this->_splitTenderIdKey, $response->getSplitTenderId());
                    $this->_registerCard($response, $orderPayment);
                    $this->_clearAssignedData($quotePayment);
                    $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_SUCCESS);
                    $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                    $exceptionMessage = $this->_wrapGatewayError($response->getResponseReasonText());
                    break;
                case self::RESPONSE_CODE_DECLINED:
                case self::RESPONSE_CODE_ERROR:
                    $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                    $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                    $exceptionMessage = $this->_wrapGatewayError($response->getResponseReasonText());
                    break;
                default:
                    $this->setPartialAuthorizationLastActionState(self::PARTIAL_AUTH_LAST_DECLINED);
                    $quotePayment->setAdditionalInformation($orderPayment->getAdditionalInformation());
                    $exceptionMessage = $this->_wrapGatewayError(
                        __('Something went wrong while authorizing the partial payment.')
                    );
            }
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }

        throw new \Magento\Payment\Model\Info\Exception($exceptionMessage);
    }

    /**
     * Return authorize payment request
     *
     * @return \Magento\Authorizenet\Model\Authorizenet\Request
     */
    protected function _getRequest()
    {
        $request = $this->_requestFactory->create()->setXVersion(
            3.1
        )->setXDelimData(
            'True'
        )->setXRelayResponse(
            'False'
        )->setXTestRequest(
            $this->getConfigData('test') ? 'TRUE' : 'FALSE'
        )->setXLogin(
            $this->getConfigData('login')
        )->setXTranKey(
            $this->getConfigData('trans_key')
        );

        return $request;
    }

    /**
     * Prepare request to gateway
     *
     * @param \Magento\Framework\Object|\Magento\Payment\Model\Info $payment
     * @return \Magento\Authorizenet\Model\Authorizenet\Request
     * @link http://www.authorize.net/support/AIM_guide.pdf
     */
    protected function _buildRequest(\Magento\Framework\Object $payment)
    {
        $order = $payment->getOrder();

        $this->setStore($order->getStoreId());

        $request = $this->_getRequest()->setXType($payment->getAnetTransType())->setXMethod(self::REQUEST_METHOD_CC);

        if ($order && $order->getIncrementId()) {
            $request->setXInvoiceNum($order->getIncrementId());
        }

        if ($payment->getAmount()) {
            $request->setXAmount($payment->getAmount(), 2);
            $request->setXCurrencyCode($order->getBaseCurrencyCode());
        }

        switch ($payment->getAnetTransType()) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                    $request->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
                }
                break;
            case self::REQUEST_TYPE_AUTH_ONLY:
                $request->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
                if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                    $request->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
                }
                break;
            case self::REQUEST_TYPE_CREDIT:
                /**
                 * Send last 4 digits of credit card number to authorize.net
                 * otherwise it will give an error
                 */
                $request->setXCardNum($payment->getCcLast4());
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_VOID:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                $request->setXTransId($payment->getXTransId());
                break;
            case self::REQUEST_TYPE_CAPTURE_ONLY:
                $request->setXAuthCode($payment->getCcAuthCode());
                break;
        }

        if ($this->getIsCentinelValidationEnabled()) {
            $params = $this->getCentinelValidator()->exportCmpiData(array());
            $request = \Magento\Framework\Object\Mapper::accumulateByMap($params, $request, $this->_centinelFieldMap);
        }

        if (!empty($order)) {
            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setXFirstName(
                    $billing->getFirstname()
                )->setXLastName(
                    $billing->getLastname()
                )->setXCompany(
                    $billing->getCompany()
                )->setXAddress(
                    $billing->getStreet(1)
                )->setXCity(
                    $billing->getCity()
                )->setXState(
                    $billing->getRegion()
                )->setXZip(
                    $billing->getPostcode()
                )->setXCountry(
                    $billing->getCountry()
                )->setXPhone(
                    $billing->getTelephone()
                )->setXFax(
                    $billing->getFax()
                )->setXCustId(
                    $order->getCustomerId()
                )->setXCustomerIp(
                    $order->getRemoteIp()
                )->setXCustomerTaxId(
                    $billing->getTaxId()
                )->setXEmail(
                    $order->getCustomerEmail()
                )->setXEmailCustomer(
                    $this->getConfigData('email_customer')
                )->setXMerchantEmail(
                    $this->getConfigData('merchant_email')
                );
            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $request->setXShipToFirstName(
                    $shipping->getFirstname()
                )->setXShipToLastName(
                    $shipping->getLastname()
                )->setXShipToCompany(
                    $shipping->getCompany()
                )->setXShipToAddress(
                    $shipping->getStreet(1)
                )->setXShipToCity(
                    $shipping->getCity()
                )->setXShipToState(
                    $shipping->getRegion()
                )->setXShipToZip(
                    $shipping->getPostcode()
                )->setXShipToCountry(
                    $shipping->getCountry()
                );
            }

            $request->setXPoNum(
                $payment->getPoNumber()
            )->setXTax(
                $order->getBaseTaxAmount()
            )->setXFreight(
                $order->getBaseShippingAmount()
            );
        }

        if ($payment->getCcNumber()) {
            $request->setXCardNum(
                $payment->getCcNumber()
            )->setXExpDate(
                sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear())
            )->setXCardCode(
                $payment->getCcCid()
            );
        }

        return $request;
    }

    /**
     * Post request to gateway and return response
     *
     * @param \Magento\Authorizenet\Model\Authorizenet\Request $request
     * @return \Magento\Authorizenet\Model\Authorizenet\Result
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _postRequest(\Magento\Framework\Object $request)
    {
        $debugData = array('request' => $request->getData());

        $result = $this->_resultFactory->create();

        $client = new \Magento\Framework\HTTP\ZendClient();

        $uri = $this->getConfigData('cgi_url');
        $client->setUri($uri ? $uri : self::CGI_URL);
        $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
        foreach ($request->getData() as $key => $value) {
            $request->setData($key, str_replace(self::RESPONSE_DELIM_CHAR, '', $value));
        }
        $request->setXDelimChar(self::RESPONSE_DELIM_CHAR);

        $client->setParameterPost($request->getData());
        $client->setMethod(\Zend_Http_Client::POST);

        try {
            $response = $client->request();
        } catch (\Exception $e) {
            $result->setResponseCode(
                -1
            )->setResponseReasonCode(
                $e->getCode()
            )->setResponseReasonText(
                $e->getMessage()
            );

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            throw new \Magento\Framework\Model\Exception($this->_wrapGatewayError($e->getMessage()));
        }

        $responseBody = $response->getBody();

        $r = explode(self::RESPONSE_DELIM_CHAR, $responseBody);

        if ($r) {
            $result->setResponseCode(
                (int)str_replace('"', '', $r[0])
            )->setResponseSubcode(
                (int)str_replace('"', '', $r[1])
            )->setResponseReasonCode(
                (int)str_replace('"', '', $r[2])
            )->setResponseReasonText(
                $r[3]
            )->setApprovalCode(
                $r[4]
            )->setAvsResultCode(
                $r[5]
            )->setTransactionId(
                $r[6]
            )->setInvoiceNumber(
                $r[7]
            )->setDescription(
                $r[8]
            )->setAmount(
                $r[9]
            )->setMethod(
                $r[10]
            )->setTransactionType(
                $r[11]
            )->setCustomerId(
                $r[12]
            )->setMd5Hash(
                $r[37]
            )->setCardCodeResponseCode(
                $r[38]
            )->setCAVVResponseCode(
                isset($r[39]) ? $r[39] : null
            )->setSplitTenderId(
                $r[52]
            )->setAccNumber(
                $r[50]
            )->setCardType(
                $r[51]
            )->setRequestedAmount(
                $r[53]
            )->setBalanceOnCard(
                $r[54]
            );
        } else {
            throw new \Magento\Framework\Model\Exception(__('Something went wrong in the payment gateway.'));
        }

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }

    /**
     * Gateway response wrapper
     *
     * @param string $text
     * @return string
     */
    protected function _wrapGatewayError($text)
    {
        return __('Gateway error: %1', $text);
    }

    /**
     * It sets card`s data into additional information of payment model
     *
     * @param \Magento\Framework\Object $response
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     */
    protected function _registerCard(\Magento\Framework\Object $response, \Magento\Sales\Model\Order\Payment $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $card = $cardsStorage->registerCard();
        $card->setRequestedAmount(
            $response->getRequestedAmount()
        )->setBalanceOnCard(
            $response->getBalanceOnCard()
        )->setLastTransId(
            $response->getTransactionId()
        )->setProcessedAmount(
            $response->getAmount()
        )->setCcType(
            $payment->getCcType()
        )->setCcOwner(
            $payment->getCcOwner()
        )->setCcLast4(
            $payment->getCcLast4()
        )->setCcExpMonth(
            $payment->getCcExpMonth()
        )->setCcExpYear(
            $payment->getCcExpYear()
        )->setCcSsIssue(
            $payment->getCcSsIssue()
        )->setCcSsStartMonth(
            $payment->getCcSsStartMonth()
        )->setCcSsStartYear(
            $payment->getCcSsStartYear()
        );

        $cardsStorage->updateCard($card);
        $this->_clearAssignedData($payment);
        return $card;
    }

    /**
     * Reset assigned data in payment info model
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return $this
     */
    private function _clearAssignedData($payment)
    {
        $payment->setCcType(
            null
        )->setCcOwner(
            null
        )->setCcLast4(
            null
        )->setCcNumber(
            null
        )->setCcCid(
            null
        )->setCcExpMonth(
            null
        )->setCcExpYear(
            null
        )->setCcSsIssue(
            null
        )->setCcSsStartMonth(
            null
        )->setCcSsStartYear(
            null
        );
        return $this;
    }

    /**
     * Add payment transaction
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param string $transactionId
     * @param string $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @param bool $message
     * @return null|\Magento\Sales\Model\Order\Payment\Transaction
     */
    protected function _addTransaction(
        \Magento\Sales\Model\Order\Payment $payment,
        $transactionId,
        $transactionType,
        array $transactionDetails = array(),
        array $transactionAdditionalInfo = array(),
        $message = false
    ) {
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false, $message);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();

        /**
         * It for self using
         */
        $transaction->setMessage($message);

        return $transaction;
    }

    /**
     * Round up and cast specified amount to float or string
     *
     * @param string|float $amount
     * @param bool $asFloat
     * @return string|float
     */
    protected function _formatAmount($amount, $asFloat = false)
    {
        $amount = sprintf('%.2F', $amount);
        // "f" depends on locale, "F" doesn't
        return $asFloat ? (double)$amount : $amount;
    }

    /**
     * If gateway actions are locked return true
     *
     * @param  \Magento\Payment\Model\Info $payment
     * @return bool
     */
    protected function _isGatewayActionsLocked($payment)
    {
        return $payment->getAdditionalInformation($this->_isGatewayActionsLockedKey);
    }

    /**
     * Process exceptions for gateway action with a lot of transactions
     *
     * @param  \Magento\Payment\Model\Info $payment
     * @param  string $messages
     * @param  bool $isSuccessfulTransactions
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _processFailureMultitransactionAction($payment, $messages, $isSuccessfulTransactions)
    {
        if ($isSuccessfulTransactions) {
            $messages[] = __(
                'Gateway actions are locked because the gateway cannot complete ' .
                'one or more of the transactions. ' .
                'Please log in to your Authorize.Net account to manually resolve the issue(s).'
            );
            /**
             * If there is successful transactions we can not to cancel order but
             * have to save information about processed transactions in order`s comments and disable
             * opportunity to voiding\capturing\refunding in future. Current order and payment will not be saved because we have to
             * load new order object and set information into this object.
             */
            $currentOrderId = $payment->getOrder()->getId();
            $copyOrder = $this->_orderFactory->create()->load($currentOrderId);
            $copyOrder->getPayment()->setAdditionalInformation($this->_isGatewayActionsLockedKey, 1);
            foreach ($messages as $message) {
                $copyOrder->addStatusHistoryComment($message);
            }
            $copyOrder->save();
        }
        throw new \Magento\Framework\Model\Exception($this->_authorizenetData->convertMessagesToMessage($messages));
    }

    /**
     * Generate checksum for object
     *
     * @param \Magento\Framework\Object $object
     * @param array $checkSumDataKeys
     * @return string
     */
    protected function _generateChecksum(\Magento\Framework\Object $object, $checkSumDataKeys = array())
    {
        $data = array();
        foreach ($checkSumDataKeys as $dataKey) {
            $data[] = $dataKey;
            $data[] = $object->getData($dataKey);
        }
        return md5(implode($data, '_'));
    }

    /**
     * This function returns full transaction details for a specified transaction ID.
     *
     * @param string $transactionId
     * @return \Magento\Framework\Object
     * @throws \Magento\Framework\Model\Exception
     * @link http://www.authorize.net/support/ReportingGuide_XML.pdf
     * @link http://developer.authorize.net/api/transaction_details/
     */
    protected function _getTransactionDetails($transactionId)
    {
        $requestBody = sprintf(
            '<?xml version="1.0" encoding="utf-8"?>' .
            '<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">' .
            '<merchantAuthentication><name>%s</name><transactionKey>%s</transactionKey></merchantAuthentication>' .
            '<transId>%s</transId>' .
            '</getTransactionDetailsRequest>',
            $this->getConfigData('login'),
            $this->getConfigData('trans_key'),
            $transactionId
        );

        $client = new \Magento\Framework\HTTP\ZendClient();
        $uri = $this->getConfigData('cgi_url_td');
        $client->setUri($uri ? $uri : self::CGI_URL_TD);
        $client->setConfig(array('timeout' => 45));
        $client->setHeaders(array('Content-Type: text/xml'));
        $client->setMethod(\Zend_Http_Client::POST);
        $client->setRawData($requestBody);

        $debugData = array('request' => $requestBody);

        try {
            $responseBody = $client->request()->getBody();
            $debugData['result'] = $responseBody;
            $this->_debug($debugData);
            libxml_use_internal_errors(true);
            $responseXmlDocument = new \Magento\Framework\Simplexml\Element($responseBody);
            libxml_use_internal_errors(false);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception(__('Payment updating error.'));
        }

        $response = new \Magento\Framework\Object();
        $response->setResponseCode(
            (string)$responseXmlDocument->transaction->responseCode
        )->setResponseReasonCode(
            (string)$responseXmlDocument->transaction->responseReasonCode
        )->setTransactionStatus(
            (string)$responseXmlDocument->transaction->transactionStatus
        );
        return $response;
    }
}
