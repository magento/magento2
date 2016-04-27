<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\Sales\Model\AbstractModel;
use Magento\Payment\Model\Method\Substitution;
use Magento\Payment\Model\InfoInterface;

/**
 *
 * Payment information model
 */
class Info extends AbstractModel implements InfoInterface
{
    /**
     * Additional information container
     *
     * @var array
     */
    protected $additionalInformation = [];

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentData;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
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
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->paymentData = $paymentData;
        $this->encryptor = $encryptor;
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
            $ccNumber = parent::getData('cc_number');
            $ccNumberEnc = parent::getData('cc_number_enc');
            if (empty($ccNumber) && !empty($ccNumberEnc)) {
                $this->setData('cc_number', $this->decrypt($ccNumberEnc));
            }
        }
        if ('cc_cid' === $key) {
            $ccCid = parent::getData('cc_cid');
            $ccCidEnc = parent::getData('cc_cid_enc');
            if (empty($ccCid) && !empty($ccCidEnc)) {
                $this->setData('cc_cid', $this->decrypt($ccCidEnc));
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
                $instance = $this->paymentData->getMethodInstance($this->getMethod());
            } catch (\UnexpectedValueException $e) {
                $instance = $this->paymentData->getMethodInstance(Substitution::CODE);
            }
            $instance->setInfoInstance($this);
            $this->setMethodInstance($instance);
        }
        return $this->getData('method_instance');
    }

    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        return $this->encryptor->encrypt($data);
    }

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        return $this->encryptor->decrypt($data);
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
        $this->initAdditionalInformation();
        if (is_array($key) && $value === null) {
            $this->additionalInformation = $key;
        } else {
            $this->additionalInformation[$key] = $value;
        }
        return $this->setData('additional_information', $this->additionalInformation);
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null)
    {
        $this->initAdditionalInformation();
        if (null === $key) {
            return $this->additionalInformation;
        }
        return isset($this->additionalInformation[$key]) ? $this->additionalInformation[$key] : null;
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($key && isset($this->additionalInformation[$key])) {
            unset($this->additionalInformation[$key]);
            return $this->setData('additional_information', $this->additionalInformation);
        } elseif (null === $key) {
            $this->additionalInformation = [];
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
        $this->initAdditionalInformation();
        return null === $key ? !empty($this->additionalInformation) : array_key_exists(
            $key,
            $this->additionalInformation
        );
    }

    /**
     * Initialize additional information container with data from model
     * if property empty
     *
     * @return void
     */
    protected function initAdditionalInformation()
    {
        $additionalInfo = $this->getData('additional_information');
        if (empty($this->additionalInformation) && $additionalInfo) {
            $this->additionalInformation = $additionalInfo;
        }
    }
}
