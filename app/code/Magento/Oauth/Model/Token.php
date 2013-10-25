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
 * oAuth token model
 *
 * @category    Magento
 * @package     Magento_Oauth
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method string getName() Consumer name (joined from consumer table)
 * @method \Magento\Oauth\Model\Resource\Token\Collection getCollection()
 * @method \Magento\Oauth\Model\Resource\Token\Collection getResourceCollection()
 * @method \Magento\Oauth\Model\Resource\Token getResource()
 * @method \Magento\Oauth\Model\Resource\Token _getResource()
 * @method int getConsumerId()
 * @method \Magento\Oauth\Model\Token setConsumerId() setConsumerId(int $consumerId)
 * @method int getAdminId()
 * @method \Magento\Oauth\Model\Token setAdminId() setAdminId(int $adminId)
 * @method int getCustomerId()
 * @method \Magento\Oauth\Model\Token setCustomerId() setCustomerId(int $customerId)
 * @method string getType()
 * @method \Magento\Oauth\Model\Token setType() setType(string $type)
 * @method string getCallbackUrl()
 * @method \Magento\Oauth\Model\Token setCallbackUrl() setCallbackUrl(string $callbackUrl)
 * @method string getCreatedAt()
 * @method \Magento\Oauth\Model\Token setCreatedAt() setCreatedAt(string $createdAt)
 * @method string getToken()
 * @method \Magento\Oauth\Model\Token setToken() setToken(string $token)
 * @method string getSecret()
 * @method \Magento\Oauth\Model\Token setSecret() setSecret(string $tokenSecret)
 * @method int getRevoked()
 * @method \Magento\Oauth\Model\Token setRevoked() setRevoked(int $revoked)
 * @method int getAuthorized()
 * @method \Magento\Oauth\Model\Token setAuthorized() setAuthorized(int $authorized)
 */
namespace Magento\Oauth\Model;

class Token extends \Magento\Core\Model\AbstractModel
{
    /**#@+
     * Token types
     */
    const TYPE_REQUEST = 'request';
    const TYPE_ACCESS = 'access';
    const TYPE_VERIFIER = 'verifier';
    /**#@- */

    /**#@+
     * Lengths of token fields
     */
    const LENGTH_TOKEN = 32;
    const LENGTH_SECRET = 32;
    const LENGTH_VERIFIER = 32;
    /**#@- */

    /**#@+
     * Customer types
     */
    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_CUSTOMER = 'customer';

    /** @var \Magento\Oauth\Helper\Service */
    protected $_oauthData;

    /** @var \Magento\Oauth\Model\Consumer\Factory */
    protected $_consumerFactory;

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
     * @param \Magento\Oauth\Model\Consumer\Factory $consumerFactory
     * @param \Magento\Oauth\Helper\Service $oauthData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Oauth\Model\Consumer\Validator\KeyLengthFactory $keyLengthFactory,
        \Magento\Url\Validator $urlValidator,
        \Magento\Oauth\Model\Consumer\Factory $consumerFactory,
        \Magento\Oauth\Helper\Service $oauthData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->keyLengthFactory = $keyLengthFactory;
        $this->urlValidator = $urlValidator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_consumerFactory = $consumerFactory;
        $this->_oauthData = $oauthData;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Oauth\Model\Resource\Token');
    }

    /**
     * "After save" actions
     *
     * @return \Magento\Oauth\Model\Token
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        //Cleanup old entries
        if ($this->_oauthData->isCleanupProbability()) {
            $this->_getResource()->deleteOldEntries($this->_oauthData->getCleanupExpirationPeriod());
        }
        return $this;
    }

    /**
     * Generate an oauth_verifier for a consumer, if the consumer doesn't already have one.
     *
     * @param int $consumerId - The id of the consumer associated with the verifier to be generated.
     * @return \Magento\Oauth\Model\Token
     */
    public function createVerifierToken($consumerId)
    {
        $tokenData = $this->getResource()
            ->selectTokenByType($consumerId, \Magento\Oauth\Model\Token::TYPE_VERIFIER);
        $this->setData($tokenData ? $tokenData : array());
        if (!$this->getId()) {
            $this->setData(array(
                'consumer_id' => $consumerId,
                'type' => \Magento\Oauth\Model\Token::TYPE_VERIFIER,
                'token' => $this->_oauthData->generateToken(),
                'secret' => $this->_oauthData->generateTokenSecret(),
                'verifier' => $this->_oauthData->generateVerifier(),
                'callback_url' => \Magento\Oauth\Helper\Service::CALLBACK_ESTABLISHED
            ));
            $this->save();
        }
        return $this;
    }

    /**
     * Authorize token
     *
     * @param int $userId Authorization user identifier
     * @param string $userType Authorization user type
     * @return \Magento\Oauth\Model\Token
     * @throws \Magento\Oauth\Exception
     */
    public function authorize($userId, $userType)
    {
        if (!$this->getId() || !$this->getConsumerId()) {
            throw new \Magento\Oauth\Exception('Token is not ready to be authorized');
        }
        if ($this->getAuthorized()) {
            throw new \Magento\Oauth\Exception('Token is already authorized');
        }
        if (self::USER_TYPE_ADMIN == $userType) {
            $this->setAdminId($userId);
        } elseif (self::USER_TYPE_CUSTOMER == $userType) {
            $this->setCustomerId($userId);
        } else {
            throw new \Magento\Oauth\Exception('User type is unknown');
        }

        $this->setVerifier($this->_oauthData->generateVerifier());
        $this->setAuthorized(1);
        $this->save();

        $this->getResource()->cleanOldAuthorizedTokensExcept($this);

        return $this;
    }

    /**
     * Convert token to access type
     *
     * @return \Magento\Oauth\Model\Token
     * @throws \Magento\Oauth\Exception
     */
    public function convertToAccess()
    {
        if (\Magento\Oauth\Model\Token::TYPE_REQUEST != $this->getType()) {
            throw new \Magento\Oauth\Exception('Can not convert due to token is not request type');
        }

        $this->setType(self::TYPE_ACCESS);
        $this->setToken($this->_oauthData->generateToken());
        $this->setSecret($this->_oauthData->generateTokenSecret());
        $this->save();

        return $this;
    }

    /**
     * Generate and save request token
     *
     * @param int $entityId Token identifier
     * @param string $callbackUrl Callback URL
     * @return \Magento\Oauth\Model\Token
     */
    public function createRequestToken($entityId, $callbackUrl)
    {
        $this->setData(array(
               'entity_id' => $entityId,
               'type' => self::TYPE_REQUEST,
               'token' => $this->_oauthData->generateToken(),
               'secret' => $this->_oauthData->generateTokenSecret(),
               'callback_url' => $callbackUrl
           ));
        $this->save();

        return $this;
    }

    /**
     * Get OAuth user type
     *
     * @return string
     * @throws \Magento\Oauth\Exception
     */
    public function getUserType()
    {
        if ($this->getAdminId()) {
            return self::USER_TYPE_ADMIN;
        } elseif ($this->getCustomerId()) {
            return self::USER_TYPE_CUSTOMER;
        } else {
            throw new \Magento\Oauth\Exception('User type is unknown');
        }
    }

    /**
     * Get string representation of token
     *
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toString($format = '')
    {
        return http_build_query(array('oauth_token' => $this->getToken(), 'oauth_token_secret' => $this->getSecret()));
    }

    /**
     * Before save actions
     *
     * @return \Magento\Oauth\Model\Consumer
     */
    protected function _beforeSave()
    {
        $this->validate();

        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(\Magento\Date::now());
        }
        parent::_beforeSave();
        return $this;
    }

    /**
     * Validate data
     *
     * @return array|bool
     * @throws \Magento\Oauth\Exception Throw exception on fail validation
     */
    public function validate()
    {
        if (\Magento\Oauth\Helper\Service::CALLBACK_ESTABLISHED != $this->getCallbackUrl()
            && !$this->urlValidator->isValid($this->getCallbackUrl())
        ) {
            $messages = $this->urlValidator->getMessages();
            throw new \Magento\Oauth\Exception(array_shift($messages));
        }

        /** @var $validatorLength \Magento\Oauth\Model\Consumer\Validator\KeyLength */
        $validatorLength = $this->keyLengthFactory->create();
        $validatorLength->setLength(self::LENGTH_SECRET);
        $validatorLength->setName('Token Secret Key');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Oauth\Exception(array_shift($messages));
        }

        $validatorLength->setLength(self::LENGTH_TOKEN);
        $validatorLength->setName('Token Key');
        if (!$validatorLength->isValid($this->getToken())) {
            $messages = $validatorLength->getMessages();
            throw new \Magento\Oauth\Exception(array_shift($messages));
        }

        if (null !== ($verifier = $this->getVerifier())) {
            $validatorLength->setLength(self::LENGTH_VERIFIER);
            $validatorLength->setName('Verifier Key');
            if (!$validatorLength->isValid($verifier)) {
                $messages = $validatorLength->getMessages();
                throw new \Magento\Oauth\Exception(array_shift($messages));
            }
        }
        return true;
    }

    /**
     * Get Token Consumer
     *
     * @return \Magento\Oauth\Model\Consumer
     */
    public function getConsumer()
    {
        if (!$this->getData('consumer')) {
            $consumer = $this->_consumerFactory->create()->load($this->getConsumerId());
            $this->setData('consumer', $consumer);
        }

        return $this->getData('consumer');
    }

    /**
     * Return the token's verifier.
     *
     * @return string
     */
    public function getVerifier()
    {
        return $this->getData('verifier');
    }

    /**
     * Set the token's verifier.
     *
     * @param string $verifier
     * @return \Magento\Oauth\Model\Token
     */
    public function setVerifier($verifier)
    {
        $this->setData('verifier', $verifier);
        return $this;
    }
}
