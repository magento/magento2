<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Block\Adminhtml;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\ConfigProviderInterface;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\AdobeImsApi\Api\UserProfileRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\JsonHexTag;

/**
 * Provides required data for the Adobe service authentication component
 *
 * @api
 */
class ReAuth extends Template
{
    private const DATA_ARGUMENT_KEY_CONFIG_PROVIDERS = 'configProviders';
    private const RESPONSE_REGEXP_PATTERN = 'auth\\[code=(success|error);message=(.+)\\]';
    private const RESPONSE_CODE_INDEX = 1;
    private const RESPONSE_MESSAGE_INDEX = 2;
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';
    private const ADOBE_IMS_JS_REAUTH = 'Magento_AdminAdobeIms/js/adobe-reAuth';
    private const ADOBE_IMS_REAUTH = 'Magento_AdminAdobeIms/reAuth';

    /**
     * @var ImsConfig
     */
    private $imsConfig;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var UserAuthorizedInterface
     */
    private $userAuthorized;

    /**
     * @var UserProfileRepositoryInterface
     */
    private $userProfileRepository;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * JsonHexTag Serializer Instance
     *
     * @var JsonHexTag
     */
    private $serializer;

    /**
     * SignIn constructor.
     *
     * @param Context $context
     * @param ImsConfig $imsConfig
     * @param UserContextInterface $userContext
     * @param UserAuthorizedInterface $userAuthorized
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param JsonHexTag $json
     * @param Auth $auth
     * @param array $data
     */
    public function __construct(
        Context $context,
        ImsConfig $imsConfig,
        UserContextInterface $userContext,
        UserAuthorizedInterface $userAuthorized,
        UserProfileRepositoryInterface $userProfileRepository,
        JsonHexTag $json,
        Auth $auth,
        array $data = []
    ) {
        $this->imsConfig = $imsConfig;
        $this->userContext = $userContext;
        $this->userAuthorized = $userAuthorized;
        $this->userProfileRepository = $userProfileRepository;
        $this->serializer = $json;
        $this->auth = $auth;
        parent::__construct($context, $data);
    }

    /**
     * Get configuration for UI component
     *
     * @return string
     */
    public function getComponentJsonConfig(): string
    {
        return $this->serializer->serialize(
            array_replace_recursive(
                $this->getDefaultComponentConfig(),
                ...$this->getExtendedComponentConfig()
            )
        );
    }

    /**
     * Get default UI component configuration
     *
     * @return array
     */
    private function getDefaultComponentConfig(): array
    {
        return [
            'component' => self::ADOBE_IMS_JS_REAUTH,
            'template' => self::ADOBE_IMS_REAUTH,
            'profileUrl' => $this->getUrl(ImsConfig::XML_PATH_PROFILE_URL),
            'logoutUrl' => $this->getUrl(ImsConfig::XML_PATH_LOGOUT_URL),
            'user' => $this->getUserData(),
            'loginConfig' => [
                'url' => $this->imsConfig->getAdminAdobeImsReAuthUrl(),
                'callbackParsingParams' => [
                    'regexpPattern' => self::RESPONSE_REGEXP_PATTERN,
                    'codeIndex' => self::RESPONSE_CODE_INDEX,
                    'messageIndex' => self::RESPONSE_MESSAGE_INDEX,
                    'successCode' => self::RESPONSE_SUCCESS_CODE,
                    'errorCode' => self::RESPONSE_ERROR_CODE
                ]
            ]
        ];
    }

    /**
     * Get UI component configuration extension specified in layout configuration for block instance
     *
     * @return array
     */
    private function getExtendedComponentConfig(): array
    {
        $configProviders = $this->getData(self::DATA_ARGUMENT_KEY_CONFIG_PROVIDERS);
        if (empty($configProviders)) {
            return [];
        }

        $configExtensions = [];
        foreach ($configProviders as $configProvider) {
            if ($configProvider instanceof ConfigProviderInterface) {
                $configExtensions[] = $configProvider->get();
            }
        }
        return $configExtensions;
    }

    /**
     * Get user profile information
     *
     * @return array
     */
    private function getUserData(): array
    {
        if (!$this->userAuthorized->execute()) {
            return $this->getDefaultUserData();
        }

        try {
            $userProfile = $this->userProfileRepository->getByUserId((int)$this->userContext->getUserId());
        } catch (NoSuchEntityException $exception) {
            return $this->getDefaultUserData();
        }

        return [
            'isAuthorized' => true,
            'name' => $userProfile->getName(),
            'email' => $userProfile->getEmail(),
            'image' => $userProfile->getImage(),
        ];
    }

    /**
     * Get default user data for not authenticated or missing user profile
     *
     * @return array
     */
    private function getDefaultUserData(): array
    {
        return [
            'isAuthorized' => false,
            'name' => '',
            'email' => '',
            'image' => '',
        ];
    }
}
