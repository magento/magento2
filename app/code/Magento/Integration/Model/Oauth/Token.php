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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Oauth;

use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Integration\Model\Resource\Oauth\Token\Collection as TokenCollection;
use Magento\Framework\Oauth\Exception as OauthException;

/**
 * oAuth token model
 *
 * @method string getName() Consumer name (joined from consumer table)
 * @method TokenCollection getCollection()
 * @method TokenCollection getResourceCollection()
 * @method \Magento\Integration\Model\Resource\Oauth\Token getResource()
 * @method \Magento\Integration\Model\Resource\Oauth\Token _getResource()
 * @method int getConsumerId()
 * @method Token setConsumerId() setConsumerId(int $consumerId)
 * @method int getAdminId()
 * @method Token setAdminId() setAdminId(int $adminId)
 * @method int getCustomerId()
 * @method Token setCustomerId() setCustomerId(int $customerId)
 * @method string getType()
 * @method Token setType() setType(string $type)
 * @method string getCallbackUrl()
 * @method Token setCallbackUrl() setCallbackUrl(string $callbackUrl)
 * @method string getCreatedAt()
 * @method Token setCreatedAt() setCreatedAt(string $createdAt)
 * @method string getToken()
 * @method Token setToken() setToken(string $token)
 * @method string getSecret()
 * @method Token setSecret() setSecret(string $tokenSecret)
 * @method int getRevoked()
 * @method Token setRevoked() setRevoked(int $revoked)
 * @method int getAuthorized()
 * @method Token setAuthorized() setAuthorized(int $authorized)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Token extends \Magento\Framework\Model\AbstractModel
{
    /**#@+
     * Token types
     */
    const TYPE_REQUEST = 'request';

    const TYPE_ACCESS = 'access';

    const TYPE_VERIFIER = 'verifier';

    /**#@- */

    /**#@+
     * Customer types
     */
    const USER_TYPE_ADMIN = 'admin';

    const USER_TYPE_CUSTOMER = 'customer';

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
     * @var \Magento\Integration\Model\Oauth\Consumer\Factory
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory
     * @param \Magento\Framework\Url\Validator $urlValidator
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory
     * @param \Magento\Integration\Helper\Oauth\Data $oauthData
     * @param OauthHelper $oauthHelper
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLengthFactory $keyLengthFactory,
        \Magento\Framework\Url\Validator $urlValidator,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Integration\Model\Oauth\Consumer\Factory $consumerFactory,
        \Magento\Integration\Helper\Oauth\Data $oauthData,
        OauthHelper $oauthHelper,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_keyLengthFactory = $keyLengthFactory;
        $this->_urlValidator = $urlValidator;
        $this->_dateTime = $dateTime;
        $this->_consumerFactory = $consumerFactory;
        $this->_oauthData = $oauthData;
        $this->_oauthHelper = $oauthHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Integration\Model\Resource\Oauth\Token');
    }

    /**
     * The "After save" actions
     *
     * @return $this
     */
    protected function _afterSave()
    {
        parent::_afterSave();

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
        $this->setData($tokenData ? $tokenData : array());
        if (!$this->getId()) {
            $this->setData(
                array(
                    'consumer_id' => $consumerId,
                    'type' => self::TYPE_VERIFIER,
                    'token' => $this->_oauthHelper->generateToken(),
                    'secret' => $this->_oauthHelper->generateTokenSecret(),
                    'verifier' => $this->_oauthHelper->generateVerifier(),
                    'callback_url' => OauthHelper::CALLBACK_ESTABLISHED
                )
            );
            $this->save();
        }
        return $this;
    }

    /**
     * Authorize token
     *
     * @param int $userId Authorization user identifier
     * @param string $userType Authorization user type
     * @return $this
     * @throws OauthException
     */
    public function authorize($userId, $userType)
    {
        if (!$this->getId() || !$this->getConsumerId()) {
            throw new OauthException('Token is not ready to be authorized');
        }
        if ($this->getAuthorized()) {
            throw new OauthException('Token is already authorized');
        }
        if (self::USER_TYPE_ADMIN == $userType) {
            $this->setAdminId($userId);
        } elseif (self::USER_TYPE_CUSTOMER == $userType) {
            $this->setCustomerId($userId);
        } else {
            throw new OauthException('User type is unknown');
        }

        $this->setVerifier($this->_oauthHelper->generateVerifier());
        $this->setAuthorized(1);
        $this->save();

        $this->getResource()->cleanOldAuthorizedTokensExcept($this);

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
            throw new OauthException('Cannot convert to access token due to token is not request type');
        }

        $this->setType(self::TYPE_ACCESS);
        $this->setToken($this->_oauthHelper->generateToken());
        $this->setSecret($this->_oauthHelper->generateTokenSecret());
        $this->save();

        return $this;
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
            array(
                'entity_id' => $entityId,
                'type' => self::TYPE_REQUEST,
                'token' => $this->_oauthHelper->generateToken(),
                'secret' => $this->_oauthHelper->generateTokenSecret(),
                'callback_url' => $callbackUrl
            )
        );
        $this->save();

        return $this;
    }

    /**
     * Get OAuth user type
     *
     * @return string
     * @throws OauthException
     */
    public function getUserType()
    {
        if ($this->getAdminId()) {
            return self::USER_TYPE_ADMIN;
        } elseif ($this->getCustomerId()) {
            return self::USER_TYPE_CUSTOMER;
        } else {
            throw new OauthException('User type is unknown');
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
     * @return $this
     */
    protected function _beforeSave()
    {
        $this->validate();

        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt($this->_dateTime->now());
        }
        parent::_beforeSave();
        return $this;
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
            throw new OauthException(array_shift($messages));
        }

        /** @var $validatorLength \Magento\Integration\Model\Oauth\Consumer\Validator\KeyLength */
        $validatorLength = $this->_keyLengthFactory->create();
        $validatorLength->setLength(OauthHelper::LENGTH_TOKEN_SECRET);
        $validatorLength->setName('Token Secret Key');
        if (!$validatorLength->isValid($this->getSecret())) {
            $messages = $validatorLength->getMessages();
            throw new OauthException(array_shift($messages));
        }

        $validatorLength->setLength(OauthHelper::LENGTH_TOKEN);
        $validatorLength->setName('Token Key');
        if (!$validatorLength->isValid($this->getToken())) {
            $messages = $validatorLength->getMessages();
            throw new OauthException(array_shift($messages));
        }

        if (null !== ($verifier = $this->getVerifier())) {
            $validatorLength->setLength(OauthHelper::LENGTH_TOKEN_VERIFIER);
            $validatorLength->setName('Verifier Key');
            if (!$validatorLength->isValid($verifier)) {
                $messages = $validatorLength->getMessages();
                throw new OauthException(array_shift($messages));
            }
        }
        return true;
    }

    /**
     * Get Token Consumer
     *
     * @return \Magento\Integration\Model\Oauth\Consumer
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
     * @return $this
     */
    public function setVerifier($verifier)
    {
        $this->setData('verifier', $verifier);
        return $this;
    }
}
