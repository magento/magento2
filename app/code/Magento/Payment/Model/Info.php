<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Payment information model
 *
 * @api
 * @since 100.0.2
 */
class Info extends AbstractExtensibleModel implements InfoInterface
{
    /**
     * Additional information container
     *
     * @var array
     */
    protected $_additionalInformation = [];

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        $this->_encryptor = $encryptor;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Retrieve data
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ('cc_number' === $key) {
            if (empty($this->_data['cc_number']) && !empty($this->_data['cc_number_enc'])) {
                $this->_data['cc_number'] = $this->decrypt($this->getCcNumberEnc());
            }
        }
        if ('cc_cid' === $key) {
            if (empty($this->_data['cc_cid']) && !empty($this->_data['cc_cid_enc'])) {
                $this->_data['cc_cid'] = $this->decrypt($this->getCcCidEnc());
            }
        }
        return parent::getData($key, $index);
    }

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethodInstance()
    {
        if (!$this->hasMethodInstance()) {
            if (!$this->getMethod()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The payment method you requested is not available.')
                );
            }

            try {
                $instance = $this->_paymentData->getMethodInstance($this->getMethod());
            } catch (\UnexpectedValueException $e) {
                $instance = $this->_paymentData->getMethodInstance(Method\Substitution::CODE);
            }

            $instance->setInfoInstance($this);
            $this->setMethodInstance($instance);
        }

        return $this->_getData('method_instance');
    }

    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return $this->_encryptor->encrypt($data);
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return $this->_encryptor->decrypt($data);
    }

    /**
     * Additional information setter
     * Updates data inside the 'additional_information' array
     * or all 'additional_information' if key is data array
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setAdditionalInformation($key, $value = null)
    {
        if (is_object($value)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The payment disallows storing objects.'));
        }
        $this->_initAdditionalInformation();
        if (is_array($key) && $value === null) {
            $this->_additionalInformation = $key;
        } else {
            $this->_additionalInformation[$key] = $value;
        }
        return $this->setData('additional_information', $this->_additionalInformation);
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null)
    {
        $this->_initAdditionalInformation();
        if (null === $key) {
            return $this->_additionalInformation;
        }
        return $this->_additionalInformation[$key] ?? null;
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null)
    {
        $this->_initAdditionalInformation();
        if ($key && isset($this->_additionalInformation[$key])) {
            unset($this->_additionalInformation[$key]);
            return $this->setData('additional_information', $this->_additionalInformation);
        } elseif (null === $key) {
            $this->_additionalInformation = [];
            return $this->unsetData('additional_information');
        }

        return $this;
    }

    /**
     * Check whether there is additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     */
    public function hasAdditionalInformation($key = null)
    {
        $this->_initAdditionalInformation();
        return null === $key ? !empty($this->_additionalInformation) : array_key_exists(
            $key,
            $this->_additionalInformation
        );
    }

    /**
     * Initialize _additionalInformation with $this->_data['additional_information'] if empty
     *
     * @return void
     */
    protected function _initAdditionalInformation()
    {
        $additionalInfo = $this->_getData('additional_information');
        if (empty($this->_additionalInformation) && $additionalInfo) {
            $this->_additionalInformation = $additionalInfo;
        }
    }
}
