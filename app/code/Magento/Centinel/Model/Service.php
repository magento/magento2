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
 * @category    Magento
 * @package     Magento_Centinel
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * 3D Secure Validation Model
 */
namespace Magento\Centinel\Model;

class Service extends \Magento\Object
{
    /**
     * Cmpi public keys
     */
    const CMPI_PARES    = 'centinel_authstatus';
    const CMPI_ENROLLED = 'centinel_mpivendor';
    const CMPI_CAVV     = 'centinel_cavv';
    const CMPI_ECI      = 'centinel_eci';
    const CMPI_XID      = 'centinel_xid';

    /**
     * State cmpi results to public map
     *
     * @var array
     */
    protected $_cmpiMap = array(
        'lookup_enrolled'      => self::CMPI_ENROLLED,
        'lookup_eci_flag'      => self::CMPI_ECI,
        'authenticate_pa_res_status' => self::CMPI_PARES,
        'authenticate_cavv'          => self::CMPI_CAVV,
        'authenticate_eci_flag'      => self::CMPI_ECI,
        'authenticate_xid'           => self::CMPI_XID,
    );

    /**
     * Is API model configured
     *
     * @var bool
     */
    protected $_isConfigured = false;

    /**
     * Validation api model
     *
     * @var \Magento\Centinel\Model\Api
     */
    protected $_api;

    /**
     * Config
     *
     * @var \Magento\Centinel\Model\Config
     */
    protected $_config;

    /**
     * Backend url
     *
     * @var \Magento\UrlInterface
     */
    protected $_backendUrl;

    /**
     * Frontend url
     *
     * @var \Magento\UrlInterface
     */
    protected $_frontendUrl;

    /**
     * Centinel session
     *
     * @var \Magento\Core\Model\Session\AbstractSession
     */
    protected $_centinelSession;

    /**
     * Session
     *
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
     * @param \Magento\Centinel\Model\Config $config
     * @param \Magento\Centinel\Model\Api $api
     * @param \Magento\UrlInterface $backendUrl
     * @param \Magento\UrlInterface $frontendUrl
     * @param \Magento\Core\Model\Session\AbstractSession $centinelSession
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Centinel\Model\StateFactory $stateFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Centinel\Model\Config $config,
        \Magento\Centinel\Model\Api $api,
        \Magento\UrlInterface $backendUrl,
        \Magento\UrlInterface $frontendUrl,
        \Magento\Core\Model\Session\AbstractSession $centinelSession,
        \Magento\Core\Model\Session $session,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Centinel\Model\StateFactory $stateFactory,
        array $data = array()
    ) {
        $this->_config = $config;
        $this->_api = $api;
        $this->_backendUrl = $backendUrl;
        $this->_frontendUrl = $frontendUrl;
        $this->_centinelSession = $centinelSession;
        $this->_session = $session;
        $this->_storeManager = $storeManager;
        $this->_stateFactory = $stateFactory;
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
     * @param string $cardType
     * @param string $cardNumber
     * @param string $cardExpMonth
     * @param string $cardExpYear
     * @param double $amount
     * @param string $currencyCode
     * @return string
     */
    protected function _generateChecksum($paymentMethodCode, $cardType, $cardNumber, $cardExpMonth, $cardExpYear, $amount, $currencyCode)
    {
        return md5(implode(func_get_args(), '_'));
    }

    /**
     * Unified validation/authentication URL getter
     *
     * @param string $suffix
     * @param bool $current
     * @return string
     */
    private function _getUrl($suffix, $current = false)
    {
        $params = array(
            '_secure'  => true,
            '_current' => $current,
            'form_key' => $this->_session->getFormKey(),
            'isIframe' => true
        );
        if ($this->_storeManager->getStore()->isAdmin()) {
            return $this->_backendUrl->getUrl('*/centinel_index/' . $suffix, $params);
        } else {
            return $this->_frontendUrl->getUrl('centinel/index/' . $suffix, $params);
        }
    }

    /**
     * Return validation api model
     *
     * @return \Magento\Centinel\Model\Api
     */
    protected function _getApi()
    {
        if ($this->_isConfigured) {
            return $this->_api;
        }

        $config = $this->_getConfig();
        $this->_api
           ->setProcessorId($config->getProcessorId())
           ->setMerchantId($config->getMerchantId())
           ->setTransactionPwd($config->getTransactionPwd())
           ->setIsTestMode($config->getIsTestMode())
           ->setDebugFlag($config->getDebugFlag())
           ->setApiEndpointUrl($this->getCustomApiEndpointUrl());
        $this->_isConfigured = true;
        return $this->_api;
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
     */
    protected function _resetValidationState()
    {
        $this->_centinelSession->setData(array());
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
        $state->setDataStorage($this->_centinelSession)
            ->setCardType($cardType)
            ->setChecksum($dataChecksum)
            ->setIsModeStrict($this->getIsModeStrict());
        return $this->_getValidationState();
    }

    /**
     * Process lookup validation and init new validation state model
     *
     * @param \Magento\Object $data
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
     * @param \Magento\Object $data
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
     * @param \Magento\Object $data
     * @throws \Magento\Core\Exception
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
                throw new \Magento\Core\Exception(__('Payment information error. Please start over.'));
            }
            if ($validationState->isAuthenticateSuccessful()) {
                return;
            }
            throw new \Magento\Core\Exception(
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
            throw new \Magento\Core\Exception(__('This card has failed validation and cannot be used.'));
        }
    }

    /**
     * Reset validation state and drop api object
     *
     * @return \Magento\Centinel\Model\Service
     */
    public function reset()
    {
        $this->_resetValidationState();
        $this->_api = null;
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
        $data = array(
            'acs_url' => $validationState->getLookupAcsUrl(),
            'pa_req' => $validationState->getLookupPayload(),
            'term_url' => $this->_getUrl('authenticationcomplete', true),
            'md' => $validationState->getLookupTransactionId()
        );
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
     * @param mixed $to
     * @param array $map
     * @return mixed $to
     */
    public function exportCmpiData($to, $map = false)
    {
        if (!$map) {
            $map = $this->_cmpiMap;
        }
        if ($validationState = $this->_getValidationState()) {
            $to = \Magento\Object\Mapper::accumulateByMap($validationState, $to, $map);
        }
        return $to;
    }
}

