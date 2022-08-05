<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth;

use Magento\Framework\Oauth\ConsumerInterface;

/**
 * Consumer model
 *
 * @api
 * @author Magento Core Team <core@magentocommerce.com>
 * @method string getName()
 * @method Consumer setName(string $name)
 * @method Consumer setKey(string $key)
 * @method Consumer setSecret(string $secret)
 * @method Consumer setCallbackUrl(string $url)
 * @method Consumer setCreatedAt(string $date)
 * @method string getUpdatedAt()
 * @method Consumer setUpdatedAt(string $date)
 * @method string getRejectedCallbackUrl()
 * @method Consumer setRejectedCallbackUrl(string $rejectedCallbackUrl)
 * @since 100.0.2
 */
class Consumer extends \Magento\Framework\Model\AbstractModel implements ConsumerInterface
{
    /**
     * @var \Magento\Framework\Url\Validator
     */
    protected $urlValidator;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength
     */
    protected $keyLengthValidator;

    /**
     * @var  \Magento\Integration\Helper\Oauth\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_dateHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength $keyLength
     * @param \Magento\Framework\Url\Validator $urlValidator
     * @param \Magento\Integration\Helper\Oauth\Data $dataHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength $keyLength,
        \Magento\Framework\Url\Validator $urlValidator,
        \Magento\Integration\Helper\Oauth\Data $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->keyLengthValidator = $keyLength;
        $this->urlValidator = $urlValidator;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\Integration\Model\ResourceModel\Oauth\Consumer::class);
    }

    /**
     * The getter function to get the new DateTime dependency
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     *
     * @deprecated 100.0.6
     */
    private function getDateHelper()
    {
        if ($this->_dateHelper === null) {
            $this->_dateHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        }
        return $this->_dateHelper;
    }

    /**
     * BeforeSave actions
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->validate();
        parent::beforeSave();
        return $this;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate()
    {
        if ($this->getCallbackUrl() || $this->getRejectedCallbackUrl()) {
            $this->setCallbackUrl($this->getCallbackUrl() !== null ? trim($this->getCallbackUrl()) : '');
            $this->setRejectedCallbackUrl(
                $this->getRejectedCallbackUrl() !== null ? trim($this->getRejectedCallbackUrl()) : ''
            );

            if ($this->getCallbackUrl() && !$this->urlValidator->isValid($this->getCallbackUrl())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Callback URL'));
            }
            if ($this->getRejectedCallbackUrl() && !$this->urlValidator->isValid($this->getRejectedCallbackUrl())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Rejected Callback URL'));
            }
        }

        $this->keyLengthValidator
            ->setLength(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_KEY)
            ->setName('Consumer Key');
        if (!$this->keyLengthValidator->isValid($this->getKey())) {
            $messages = $this->keyLengthValidator->getMessages();
            throw new \Magento\Framework\Exception\LocalizedException(__(array_shift($messages)));
        }

        $this->keyLengthValidator
            ->setLength(\Magento\Framework\Oauth\Helper\Oauth::LENGTH_CONSUMER_SECRET)
            ->setName('Consumer Secret');
        if (!$this->keyLengthValidator->isValid($this->getSecret())) {
            $messages = $this->keyLengthValidator->getMessages();
            throw new \Magento\Framework\Exception\LocalizedException(__(array_shift($messages)));
        }
        return true;
    }

    /**
     * Load consumer data by consumer key.
     *
     * @param string $key
     * @return $this
     */
    public function loadByKey($key)
    {
        return $this->load($key, 'key');
    }

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->getData('key');
    }

    /**
     * @inheritDoc
     */
    public function getSecret()
    {
        return $this->getData('secret');
    }

    /**
     * @inheritDoc
     */
    public function getCallbackUrl()
    {
        return $this->getData('callback_url');
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritDoc
     */
    public function isValidForTokenExchange()
    {
        $expiry = $this->dataHelper->getConsumerExpirationPeriod();
        $currentTimestamp = $this->getDateHelper()->gmtTimestamp();
        $updatedTimestamp = $this->getDateHelper()->gmtTimestamp($this->getUpdatedAt());
        return $expiry > ($currentTimestamp - $updatedTimestamp);
    }
}
