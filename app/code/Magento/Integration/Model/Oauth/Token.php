<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Oauth\Exception as OauthException;
use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Integration\Api\Data\UserTokenParametersInterfaceFactory;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenIssuerInterface;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection as TokenCollection;

/**
 * oAuth token model
 *
 * @method string getName() Consumer name (joined from consumer table)
 * @method int getConsumerId()
 * @method Token setConsumerId(int $consumerId)
 * @method int getAdminId()
 * @method Token setAdminId(int $adminId)
 * @method int getCustomerId()
 * @method Token setCustomerId(int $customerId)
 * @method int getUserType()
 * @method Token setUserType(int $userType)
 * @method string getType()
 * @method Token setType(string $type)
 * @method string getCallbackUrl()
 * @method Token setCallbackUrl(string $callbackUrl)
 * @method string getCreatedAt()
 * @method Token setCreatedAt(string $createdAt)
 * @method string getToken()
 * @method Token setToken(string $token)
 * @method string getSecret()
 * @method Token setSecret(string $tokenSecret)
 * @method int getRevoked()
 * @method Token setRevoked(int $revoked)
 * @method int getAuthorized()
 * @method Token setAuthorized(int $authorized)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Token extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Token types
     */
    public const TYPE_REQUEST = 'request';

    public const TYPE_ACCESS = 'access';

    public const TYPE_VERIFIER = 'verifier';

    /**#@- */

    /**
     * @var OauthHelper
     */
    protected $_oauthHelper;

    /**
     * @var \Magento\Integration\Helper\Oauth\Data
     */
    protected $_oauthData;

    /**
     * @var \Magento\Integration\Model\Oauth\ConsumerFactory
     */
    protected $_consumerFactory;

    /**
     * @var \Magento\Framework\Url\Validator
     */
    protected $_urlValidator;

    /**
     * @var Consumer\Validator\KeyLengthFactory
     */
    protected $_keyLengthFactory;

    /**
     * @var UserTokenReaderInterface
     */
    private $reader;

    /**
     * @var UserTokenIssuerInterface
     */
    private $issuer;

    /**
     * @var UserTokenParametersInterfaceFactory
     */
    private $tokenParamsFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory
     * @param \Magento\Framework\Url\Validator $urlValidator
     * @param \Magento\Integration\Model\Oauth\ConsumerFactory $consumerFactory
     * @param \Magento\Integration\Helper\Oauth\Data $oauthData
     * @param OauthHelper $oauthHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param UserTokenReaderInterface|null $reader
     * @param UserTokenIssuerInterface|null $issuer
     * @param UserTokenParametersInterfaceFactory|null $paramsFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory,
        \Magento\Framework\Url\Validator $urlValidator,
        \Magento\Integration\Model\Oauth\ConsumerFactory $consumerFactory,
        \Magento\Integration\Helper\Oauth\Data $oauthData,
        OauthHelper $oauthHelper,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?UserTokenReaderInterface $reader = null,
        ?UserTokenIssuerInterface $issuer = null,
        ?UserTokenParametersInterfaceFactory $paramsFactory = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_keyLengthFactory = $keyLengthFactory;
        $this->_urlValidator = $urlValidator;
        $this->_consumerFactory = $consumerFactory;
        $this->_oauthData = $oauthData;
        $this->_oauthHelper = $oauthHelper;
        $this->reader = $reader ?? ObjectManager::getInstance()->get(UserTokenReaderInterface::class);
        $this->issuer = $issuer ?? ObjectManager::getInstance()->get(UserTokenIssuerInterface::class);
        $this->tokenParamsFactory = $paramsFactory ??
            ObjectManager::getInstance()->get(UserTokenParametersInterfaceFactory::class);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Integration\Model\ResourceModel\Oauth\Token::class);
    }

    /**
     * The "After save" actions
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        // Cleanup old entries
        if ($this->_oauthData->isCleanupProbability()) {
            $this->_getResource()->deleteOldEntries($this->_oauthData->getCleanupExpirationPeriod());
        }
        return $this;
    }

    /**
     * Generate an oauth_verifier for a consumer, if the consumer doesn't already have one.
     *
     * @param int $consumerId - The id of the consumer associated with the verifier to be generated.
     * @return $this
     */
    public function createVerifierToken($consumerId)
    {
        $tokenData = $this->getResource()->selectTokenByType($consumerId, self::TYPE_VERIFIER);
        $this->setData($tokenData ? $tokenData : []);
        if (!$this->getId()) {
            $this->setData(
                [
                    'consumer_id' => $consumerId,
                    'type' => self::TYPE_VERIFIER,
                    'token' => $this->_oauthHelper->generateToken(),
                    'secret' => $this->_oauthHelper->generateTokenSecret(),
                    'verifier' => $this->_oauthHelper->generateVerifier(),
                    'callback_url' => OauthHelper::CALLBACK_ESTABLISHED,
                    'user_type' => UserContextInterface::USER_TYPE_INTEGRATION, //As of now only integrations use Oauth
                ]
            );
            $this->validate();
            $this->save();
        }
        return $this;
    }

    /**
     * Convert token to access type
     *
     * @return $this
     * @throws OauthException
     */
    public function convertToAccess()
    {
        if (self::TYPE_REQUEST != $this->getType()) {
            throw new OauthException(__('Cannot convert to access token due to token is not request type'));
        }
        return $this->saveAccessToken(UserContextInterface::USER_TYPE_INTEGRATION);
    }

    /**
     * Create access token for a admin
     *
     * @param int $userId
     * @return $this
     * @deprecated New proper SPI for warking with tokens has been introduced.
     * @see UserTokenIssuerInterface
     */
    public function createAdminToken($userId)
    {
        return $this->loadByToken(
            $this->issuer->create(
                new CustomUserContext((int) $userId, UserContextInterface::USER_TYPE_ADMIN),
                $this->tokenParamsFactory->create()
            )
        );
    }

    /**
     * Create access token for a customer
     *
     * @param int $userId
     * @return $this
     * @deprecated New proper SPI for warking with tokens has been introduced.
     * @see UserTokenIssuerInterface
     */
    public function createCustomerToken($userId)
    {
        return $this->loadByToken(
            $this->issuer->create(
                new CustomUserContext((int) $userId, UserContextInterface::USER_TYPE_CUSTOMER),
                $this->tokenParamsFactory->create()
            )
        );
    }

    /**
     * Generate and save request token
     *
     * @param int $entityId Token identifier
     * @param string $callbackUrl Callback URL
     * @return $this
     */
    public function createRequestToken($entityId, $callbackUrl)
    {
        $callbackUrl = !empty($callbackUrl) ? $callbackUrl : OauthHelper::CALLBACK_ESTABLISHED;
        $this->setData(
            [
                'entity_id' => $entityId,
                'type' => self::TYPE_REQUEST,
                'token' => $this->_oauthHelper->generateToken(),
                'secret' => $this->_oauthHelper->generateTokenSecret(),
                'callback_url' => $callbackUrl,
            ]
        );
        $this->validate();
        $this->save();

        return $this;
    }

    /**
     * Get string representation of token
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __toString()
    {
        return http_build_query(['oauth_token' => $this->getToken(), 'oauth_token_secret' => $this->getSecret()]);
    }

    /**
     * Validate data
     *
     * @return bool
     * @throws OauthException Throw exception on fail validation
     */
    public function validate()
    {
        if (OauthHelper::CALLBACK_ESTABLISHED != $this->getCallbackUrl() && !$this->_urlValidator->isValid(
            $this->getCallbackUrl()
        )
        ) {
            $messages = $this->_urlValidator->getMessages();
            throw new OauthException(__(array_shift($messages)));
        }

        /** @var $validatorLength \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength */
        $validatorLength = $this->_keyLengthFactory->create();
        $validatorLength->setLength(OauthHelper::LENGTH_TOKEN_SECRET);
        $validatorLength->setName('Token Secret Key');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new OauthException(__(array_shift($messages)));
        }

        $validatorLength->setLength(OauthHelper::LENGTH_TOKEN);
        $validatorLength->setName('Token Key');
        if (!$validatorLength->isValid($this->getToken())) {
            $messages = $validatorLength->getMessages();
            throw new OauthException(__(array_shift($messages)));
        }

        if (null !== ($verifier = $this->getVerifier())) {
            $validatorLength->setLength(OauthHelper::LENGTH_TOKEN_VERIFIER);
            $validatorLength->setName('Verifier Key');
            if (!$validatorLength->isValid($verifier)) {
                $messages = $validatorLength->getMessages();
                throw new OauthException(__(array_shift($messages)));
            }
        }
        return true;
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
     * Generate and save access token for a given user type
     *
     * @param int $userType
     * @return $this
     */
    protected function saveAccessToken($userType)
    {
        $this->setUserType($userType);
        $this->setType(self::TYPE_ACCESS);
        $this->setToken($this->_oauthHelper->generateToken());
        $this->setSecret($this->_oauthHelper->generateTokenSecret());
        return $this->save();
    }

    /**
     * Get token by consumer and user type
     *
     * @param int $consumerId
     * @param int $userType
     * @return $this
     */
    public function loadByConsumerIdAndUserType($consumerId, $userType)
    {
        $tokenData = $this->getResource()->selectTokenByConsumerIdAndUserType($consumerId, $userType);
        $this->setData($tokenData ? $tokenData : []);
        $this->getResource()->afterLoad($this);
        return $this;
    }

    /**
     * Get token by admin id
     *
     * @param int $adminId
     * @return $this
     */
    public function loadByAdminId($adminId)
    {
        $tokenData = $this->getResource()->selectTokenByAdminId($adminId);
        $this->setData($tokenData ? $tokenData : []);
        return $this;
    }

    /**
     * Get token by customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        $tokenData = $this->getResource()->selectTokenByCustomerId($customerId);
        $this->setData($tokenData ? $tokenData : []);
        return $this;
    }

    /**
     * Load token data by token.
     *
     * @param string $token
     * @return $this
     * @deprecated Proper SPI for managing tokens was introduced.
     * @see UserTokenReaderInterface
     */
    public function loadByToken($token)
    {
        $data = $this->load($token, 'token');
        if ($data->getId()) {
            return $data;
        }
        try {
            $data = $this->reader->read($token);
        } catch (UserTokenException $exception) {
            //Token is not valid, keeping this model's data empty
            return $this;
        }

        $this->setUserType($data->getUserContext()->getUserType());
        if ($data->getUserContext()->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $this->setCustomerId($data->getUserContext()->getUserId());
        } else {
            $this->setAdminId($data->getUserContext()->getUserId());
        }
        $this->setId(PHP_INT_MAX);
        $this->setToken($token);
        $this->setCreatedAt($data->getData()->getIssued()->format('Y-m-d H:i:s'));

        return $this;
    }
}
