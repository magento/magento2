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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application model
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method \Magento\Oauth\Model\Resource\Consumer _getResource()
 * @method \Magento\Oauth\Model\Resource\Consumer getResource()
 * @method \Magento\Oauth\Model\Resource\Consumer\Collection getCollection()
 * @method \Magento\Oauth\Model\Resource\Consumer\Collection getResourceCollection()
 * @method string getName()
 * @method \Magento\Oauth\Model\Consumer setName() setName(string $name)
 * @method \Magento\Oauth\Model\Consumer setKey() setKey(string $key)
 * @method \Magento\Oauth\Model\Consumer setSecret() setSecret(string $secret)
 * @method \Magento\Oauth\Model\Consumer setCallbackUrl() setCallbackUrl(string $url)
 * @method string getCreatedAt()
 * @method \Magento\Oauth\Model\Consumer setCreatedAt() setCreatedAt(string $date)
 * @method string getUpdatedAt()
 * @method \Magento\Oauth\Model\Consumer setUpdatedAt() setUpdatedAt(string $date)
 * @method string getRejectedCallbackUrl()
 * @method \Magento\Oauth\Model\Consumer setRejectedCallbackUrl() setRejectedCallbackUrl(string $rejectedCallbackUrl)
 * @method string getHttpPostUrl()
 * @method \Magento\Oauth\Model\Consumer setHttpPostUrl() setHttpPostUrl(string $httpPostUrl)
 */
namespace Magento\Oauth\Model;

class Consumer extends \Magento\Core\Model\AbstractModel
{
    /**
     * Key hash length
     */
    const KEY_LENGTH = 32;

    /**
     * Secret hash length
     */
    const SECRET_LENGTH = 32;

    /**
     * @var \Magento\Url\Validator
     */
    protected $urlValidator;

    /**
     * @var Consumer\Validator\KeyLengthFactory
     */
    protected $keyLengthFactory;

    /**
     * @param \Magento\Oauth\Model\Consumer\Validator\KeyLengthFactory $keyLengthFactory
     * @param \Magento\Url\Validator $urlValidator
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Oauth\Model\Consumer\Validator\KeyLengthFactory $keyLengthFactory,
        \Magento\Url\Validator $urlValidator,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->keyLengthFactory = $keyLengthFactory;
        $this->urlValidator = $urlValidator;
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
        $this->_init('Magento\Oauth\Model\Resource\Consumer');
    }

    /**
     * BeforeSave actions
     *
     * @return \Magento\Oauth\Model\Consumer
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setUpdatedAt(time());
        }
        $this->validate();
        parent::_beforeSave();
        return $this;
    }

    /**
     * Validate data
     *
     * @return array|bool
     * @throw \Magento\Core\Exception|Exception   Throw exception on fail validation
     */
    public function validate()
    {
        if ($this->getCallbackUrl() || $this->getRejectedCallbackUrl()) {
            $this->setCallbackUrl(trim($this->getCallbackUrl()));
            $this->setRejectedCallbackUrl(trim($this->getRejectedCallbackUrl()));

            if ($this->getCallbackUrl() && !$this->urlValidator->isValid($this->getCallbackUrl())) {
                throw new \Magento\Core\Exception(__('Invalid Callback URL'));
            }
            if ($this->getRejectedCallbackUrl() && !$this->urlValidator->isValid($this->getRejectedCallbackUrl())) {
                throw new \Magento\Core\Exception(__('Invalid Rejected Callback URL'));
            }
        }

        /** @var $validatorLength \Magento\Oauth\Model\Consumer\Validator\KeyLength */
        $validatorLength = $this->keyLengthFactory->create(
            array('options' => array('length' => self::KEY_LENGTH))
        );

        $validatorLength->setName('Consumer Key');
        if (!$validatorLength->isValid($this->getKey())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Core\Exception(array_shift($messages));
        }

        $validatorLength->setLength(self::SECRET_LENGTH);
        $validatorLength->setName('Consumer Secret');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Core\Exception(array_shift($messages));
        }
        return true;
    }

    /**
     * Load consumer by key.
     *
     * @param string $key
     * @return \Magento\Oauth\Model\Consumer
     */
    public function loadByKey($key)
    {
        return $this->load($key, 'key');
    }

    /**
     * Get consumer key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getData('key');
    }

    /**
     * Get consumer secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->getData('secret');
    }

    /**
     * Get consumer callback URL.
     *
     * @return string
     */
    public function getCallBackUrl()
    {
        return $this->getData('callback_url');
    }
}
