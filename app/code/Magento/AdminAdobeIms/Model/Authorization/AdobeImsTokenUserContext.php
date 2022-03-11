<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\Data\UserProfileInterface;
use Magento\AdobeImsApi\Api\Data\UserProfileInterfaceFactory;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Jwt\JwkFactory;
use Magento\Framework\Jwt\Jws\JwsSignatureJwks;
use Magento\Framework\Jwt\JwtManagerInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Framework\Webapi\Request;

/**
 * A user context determined by Adobe IMS tokens in a HTTP request Authorization header.
 */
class AdobeImsTokenUserContext implements UserContextInterface
{
    private $cacheIdPrefix = 'AdminAdobeIms_';
    private $cacheId = '';

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var bool
     */
    protected $isRequestProcessed;

    protected Request $request;
    protected ImsConnection $imsConnection;
    protected ImsConfig $imsConfig;
    private JwkFactory $jwkFactory;
    private JwtManagerInterface $jwtManager;
    private UserProfileRepositoryInterface $userProfileRepository;
    private UserProfileInterfaceFactory $userProfileFactory;
    private User $adminUser;
    private CacheInterface $cache;

    /**
     * @param Request $request
     * @param ImsConnection $imsConnection
     * @param ImsConfig $imsConfig
     * @param JwkFactory $jwkFactory
     * @param JwtManagerInterface $jwtManager
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param UserProfileInterfaceFactory $userProfileFactory
     * @param User $adminUser
     * @param CacheInterface $cache
     */
    public function __construct(
        Request $request,
        ImsConnection $imsConnection,
        ImsConfig $imsConfig,
        JwkFactory $jwkFactory,
        JwtManagerInterface $jwtManager,
        UserProfileRepositoryInterface $userProfileRepository,
        UserProfileInterfaceFactory $userProfileFactory,
        User $adminUser,
        CacheInterface $cache
    ) {
        $this->request = $request;
        $this->imsConnection = $imsConnection;
        $this->imsConfig = $imsConfig;
        $this->jwkFactory = $jwkFactory;
        $this->jwtManager = $jwtManager;
        $this->userProfileRepository = $userProfileRepository;
        $this->userProfileFactory = $userProfileFactory;
        $this->adminUser = $adminUser;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function getUserId()
    {
        if (!$this->userId) {
            $this->processRequest();
        }

        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_ADMIN;
    }

    /**
     * Finds the bearer token and looks up the value.
     *
     * @return void
     */
    protected function processRequest()
    {
        if (!$this->imsConfig->enabled()) {
            return;
        }
        if (!$bearerToken = $this->getRequestedToken()) {
            return;
        }

        try {
            if (!$jwk = $this->getJWK($bearerToken)) {
                return;
            }

            $jwt = $this->jwtManager->read($bearerToken, ['RS256' => new JwsSignatureJwks($jwk)]);

            /** @var ClaimsPayloadInterface $payload */
            $payload = $jwt->getPayload();
            $claims = $payload->getClaims();
            if (empty($claims['user_id']) || empty($claims['user_id']->getValue())) {
                throw new InvalidArgumentException(__('user_id not provided by the received JWT'));
            }

            $adobeUserId = $claims['user_id']->getValue();
            $userProfile = $this->userProfileRepository->getByAdobeUserId($adobeUserId);
            if ($userProfile->getId()) {
                $adminUserId = (int) $userProfile->getData('admin_user_id');
            } else {
                $profile = $this->imsConnection->getProfile($bearerToken);
                if (empty($profile['email'])) {
                    throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
                }
                $adminUser = $this->adminUser->loadByEmail($profile['email']);
                if (empty($adminUser['user_id'])) {
                    throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
                }

                $adminUserId = (int) $adminUser['user_id'];
                $profile['adobe_user_id'] = $adobeUserId;

                $userProfileInterface = $this->getUserProfileInterface($adminUserId);
                $this->userProfileRepository->save($this->updateUserProfile($userProfileInterface, $profile));
            }
        } catch (AuthenticationException $e) {
            $this->isRequestProcessed = true;
            return;
        }

        $this->userId = $adminUserId;
        $this->isRequestProcessed = true;
    }

    protected function getRequestedToken()
    {
        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            $this->isRequestProcessed = true;
            return false;
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            $this->isRequestProcessed = true;
            return false;
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== 'bearer') {
            $this->isRequestProcessed = true;
            return false;
        }

        return $headerPieces[1];
    }

    protected function getJWK($bearerToken)
    {
        list($header) = explode(".", "$bearerToken");
        $decodedAdobeImsHeader = json_decode(base64_decode($header), true);

        if (!isset($decodedAdobeImsHeader['x5u'])) {
            return false;
        }

        $certificateFileName = $decodedAdobeImsHeader['x5u'];
        $this->setCertificateCacheId($certificateFileName);

        if (!$certificateValue = $this->loadCertificateFromCache()) {
            $certificateUrl = $this->imsConfig->getCertificateUrl($certificateFileName);
            if (!$certificateValue = file_get_contents($certificateUrl)) {
                return false;
            }
            $this->saveCertificateInCache($certificateValue);
        }

        return $this->jwkFactory->createVerifyRs256($certificateValue);
    }

    /**
     * @return string
     */
    public function loadCertificateFromCache()
    {
        return $this->cache->load($this->cacheId);
    }

    /**
     * @param $certificateValue
     */
    public function saveCertificateInCache($certificateValue)
    {
        $this->cache->save($certificateValue, $this->cacheId, [], 86400);
    }

    /**
     * Cache Id is based on prefix that is equal to module name
     * and certificate file name that is in token header
     *
     * @param $certificateFileName
     */
    protected function setCertificateCacheId($certificateFileName)
    {
        $this->cacheId = $this->cacheIdPrefix . $certificateFileName;
    }

    /**
     * Get user profile entity
     *
     * @param int $adminUserId
     * @return UserProfileInterface
     */
    protected function getUserProfileInterface(int $adminUserId): UserProfileInterface
    {
        try {
            return $this->userProfileRepository->getByUserId($adminUserId);
        } catch (NoSuchEntityException $exception) {
            return $this->userProfileFactory->create(
                [
                    'data' => [
                        'admin_user_id' => $adminUserId
                    ]
                ]
            );
        }
    }

    /**
     * @param UserProfileInterface $userProfileInterface
     * @param array $profile
     * @return UserProfileInterface
     */
    protected function updateUserProfile(
        UserProfileInterface $userProfileInterface,
        array $profile
    ): UserProfileInterface {
        $userProfileInterface->setName($profile['name'] ?? '');
        $userProfileInterface->setEmail($profile['email'] ?? '');
        $userProfileInterface->setImage('');
        $userProfileInterface->setAdobeUserId($profile['adobe_user_id']);

        return $userProfileInterface;
    }
}
