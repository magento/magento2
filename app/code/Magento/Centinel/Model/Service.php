<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Model;

/**
 * 3D Secure Validation Model
 */
class Service extends \Magento\Framework\Object
{
    /**
     * Cmpi public keys
     */
    const CMPI_PARES = 'centinel_authstatus';

    const CMPI_ENROLLED = 'centinel_mpivendor';

    const CMPI_CAVV = 'centinel_cavv';

    const CMPI_ECI = 'centinel_eci';

    const CMPI_XID = 'centinel_xid';

    /**
     * State cmpi results to public map
     *
     * @var array
     */
    protected $_cmpiMap = [
        'lookup_enrolled' => self::CMPI_ENROLLED,
        'lookup_eci_flag' => self::CMPI_ECI,
        'authenticate_pa_res_status' => self::CMPI_PARES,
        'authenticate_cavv' => self::CMPI_CAVV,
        'authenticate_eci_flag' => self::CMPI_ECI,
        'authenticate_xid' => self::CMPI_XID,
    ];

    /**
     * Validation api model factory
     *
     * @var \Magento\Centinel\Model\Api
     */
    protected $_apiFactory;

    /**
     * Config
     *
     * @var \Magento\Centinel\Model\Config
     */
    protected $_config;

    /**
     * Backend url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Centinel session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_centinelSession;

    /**
     * State factory
     *
     * @var \Magento\Centinel\Model\StateFactory
     */
    protected $_stateFactory;

    /**
     * Validation state model
     *
     * @var \Magento\Centinel\Model\AbstractState
     */
    protected $_validationState;

    /**
     * Url prefix
     *
     * @var string
     */
    protected $_urlPrefix;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\Centinel\Model\Config $config
     * @param \Magento\Centinel\Model\ApiFactory $apiFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Session\SessionManagerInterface $centinelSession
     * @param \Magento\Centinel\Model\StateFactory $stateFactory
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param string $urlPrefix
     * @param array $data
     */
    public function __construct(
        \Magento\Centinel\Model\Config $config,
        \Magento\Centinel\Model\ApiFactory $apiFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Session\SessionManagerInterface $centinelSession,
        \Magento\Centinel\Model\StateFactory $stateFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        $urlPrefix = 'centinel/index/',
        array $data = []
    ) {
        $this->_config = $config;
        $this->_apiFactory = $apiFactory;
        $this->_url = $url;
        $this->_centinelSession = $centinelSession;
        $this->_stateFactory = $stateFactory;
        $this->formKey = $formKey;
        $this->_urlPrefix = $urlPrefix;
        parent::__construct($data);
    }

    /**
     * Return value from section of centinel config
     *
     * @return \Magento\Centinel\Model\Config
     */
    protected function _getConfig()
    {
        return $this->_config->setStore($this->getStore());
    }

    /**
     * Generate checksum from all passed parameters
     *
     * @param string $paymentMethodCode
     * @param string $cardType
     * @param string $cardNumber
     * @param string $cardExpMonth
     * @param string $cardExpYear
     * @param float $amount
     * @param string $currencyCode
     * @return string
     */
    protected function _generateChecksum(
        $paymentMethodCode,
        $cardType,
        $cardNumber,
        $cardExpMonth,
        $cardExpYear,
        $amount,
        $currencyCode
    ) {
        return md5(implode(func_get_args(), '_'));
    }

    /**
     * Unified validation/authentication URL getter
     *
     * @param string $suffix
     * @param bool $current
     * @return string
     */
    protected function _getUrl($suffix, $current = false)
    {
        $params = [
            '_secure' => true,
            '_current' => $current,
            'form_key' => $this->formKey->getFormKey(),
            'isIframe' => true,
        ];
        return $this->_url->getUrl($this->_urlPrefix . $suffix, $params);
    }

    /**
     * Return validation api model
     *
     * @return \Magento\Centinel\Model\Api
     */
    protected function _getApi()
    {
        $config = $this->_getConfig();
        $api = $this->_apiFactory->create();
        $api->setProcessorId(
            $config->getProcessorId()
        )->setMerchantId(
            $config->getMerchantId()
        )->setTransactionPwd(
            $config->getTransactionPwd()
        )->setIsTestMode(
            $config->getIsTestMode()
        )->setDebugFlag(
            $config->getDebugFlag()
        )->setApiEndpointUrl(
            $this->getCustomApiEndpointUrl()
        );
        return $api;
    }

    /**
     * Return validation state model
     *
     * @param string $cardType
     * @return \Magento\Centinel\Model\AbstractState
     */
    protected function _getValidationState($cardType = null)
    {
        $type = $cardType ? $cardType : $this->_centinelSession->getData('card_type');
        if (!$this->_validationState && $type) {
            $model = $this->_stateFactory->createState($type);
            if (!$model) {
                return false;
            }
            $model->setDataStorage($this->_centinelSession);
            $this->_validationState = $model;
        }
        return $this->_validationState;
    }

    /**
     * Drop validation state model
     *
     * @return void
     */
    protected function _resetValidationState()
    {
        $this->_centinelSession->setData([]);
        $this->_validationState = false;
    }

    /**
     * Drop old and init new validation state model
     *
     * @param string $cardType
     * @param string $dataChecksum
     * @return \Magento\Centinel\Model\AbstractState
     */
    protected function _initValidationState($cardType, $dataChecksum)
    {
        $this->_resetValidationState();
        $state = $this->_stateFactory->createState($cardType);
        $state->setDataStorage(
            $this->_centinelSession
        )->setCardType(
            $cardType
        )->setChecksum(
            $dataChecksum
        )->setIsModeStrict(
            $this->getIsModeStrict()
        );
        return $this->_getValidationState();
    }

    /**
     * Process lookup validation and init new validation state model
     *
     * @param \Magento\Framework\Object $data
     * @return void
     */
    public function lookup($data)
    {
        $newChecksum = $this->_generateChecksum(
            $data->getPaymentMethodCode(),
            $data->getCardType(),
            $data->getCardNumber(),
            $data->getCardExpMonth(),
            $data->getCardExpYear(),
            $data->getAmount(),
            $data->getCurrencyCode()
        );

        $validationState = $this->_initValidationState($data->getCardType(), $newChecksum);

        $api = $this->_getApi();
        $result = $api->callLookup($data);
        $validationState->setLookupResult($result);
    }

    /**
     * Process authenticate validation
     *
     * @param \Magento\Framework\Object $data
     * @return void
     * @throws \Exception
     */
    public function authenticate($data)
    {
        $validationState = $this->_getValidationState();
        if (!$validationState || $data->getTransactionId() != $validationState->getLookupTransactionId()) {
            throw new \Exception('Authentication impossible: transaction id or validation state is wrong.');
        }

        $api = $this->_getApi();
        $result = $api->callAuthentication($data);
        $validationState->setAuthenticateResult($result);
        if (!$validationState->isAuthenticateSuccessful()) {
            $this->reset();
        }
    }

    /**
     * Validate payment data
     *
     * This check is performed on payment information submission, as well as on placing order.
     * Workflow state is stored validation state model
     *
     * @param \Magento\Framework\Object $data
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function validate($data)
    {
        $newChecksum = $this->_generateChecksum(
            $data->getPaymentMethodCode(),
            $data->getCardType(),
            $data->getCardNumber(),
            $data->getCardExpMonth(),
            $data->getCardExpYear(),
            $data->getAmount(),
            $data->getCurrencyCode()
        );

        $validationState = $this->_getValidationState($data->getCardType());
        if (!$validationState) {
            $this->_resetValidationState();
            return;
        }

        // check whether is authenticated before placing order
        if ($this->getIsPlaceOrder()) {
            if ($validationState->getChecksum() != $newChecksum) {
                throw new \Magento\Framework\Model\Exception(__('Payment information error. Please start over.'));
            }
            if ($validationState->isAuthenticateSuccessful()) {
                return;
            }
            throw new \Magento\Framework\Model\Exception(
                __('Please verify the card with the issuer bank before placing the order.')
            );
        } else {
            if ($validationState->getChecksum() != $newChecksum || !$validationState->isLookupSuccessful()) {
                $this->lookup($data);
                $validationState = $this->_getValidationState();
            }
            if ($validationState->isLookupSuccessful()) {
                return;
            }
            throw new \Magento\Framework\Model\Exception(__('This card has failed validation and cannot be used.'));
        }
    }

    /**
     * Reset validation state and drop api object
     *
     * @return $this
     */
    public function reset()
    {
        $this->_resetValidationState();
        return $this;
    }

    /**
     * Return URL for authentication
     *
     * @return string
     */
    public function getAuthenticationStartUrl()
    {
        return $this->_getUrl('authenticationstart');
    }

    /**
     * Return URL for validation
     *
     * @return string
     */
    public function getValidatePaymentDataUrl()
    {
        return $this->_getUrl('validatepaymentdata');
    }

    /**
     * If authenticate is should return true
     *
     * @return bool
     */
    public function shouldAuthenticate()
    {
        $validationState = $this->_getValidationState();
        return $validationState && $validationState->isAuthenticateAllowed();
    }

    /**
     * Return data for start authentication (redirect customer to bank page)
     *
     * @return array
     */
    public function getAuthenticateStartData()
    {
        $validationState = $this->_getValidationState();
        if (!$validationState && $this->shouldAuthenticate()) {
            throw new \Exception('Authentication impossible: validation state is wrong.');
        }
        $data = [
            'acs_url' => $validationState->getLookupAcsUrl(),
            'pa_req' => $validationState->getLookupPayload(),
            'term_url' => $this->_getUrl('authenticationcomplete', true),
            'md' => $validationState->getLookupTransactionId(),
        ];
        return $data;
    }

    /**
     * If authenticate is successful return true
     *
     * @return bool
     */
    public function isAuthenticateSuccessful()
    {
        $validationState = $this->_getValidationState();
        return $validationState && $validationState->isAuthenticateSuccessful();
    }

    /**
     * Export cmpi lookups and authentication information stored in session into array
     *
     * @param array|object $to
     * @param array|bool $map
     * @return array|object
     */
    public function exportCmpiData($to, $map = false)
    {
        if (!$map) {
            $map = $this->_cmpiMap;
        }
        if ($validationState = $this->_getValidationState()) {
            $to = \Magento\Framework\Object\Mapper::accumulateByMap($validationState, $to, $map);
        }
        return $to;
    }
}
