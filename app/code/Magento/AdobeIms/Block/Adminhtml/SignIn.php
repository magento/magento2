<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Block\Adminhtml;

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
class SignIn extends Template
{
    public const DATA_ARGUMENT_KEY_CONFIG_PROVIDERS = 'configProviders';
    public const RESPONSE_REGEXP_PATTERN = 'auth\\[code=(success|error);message=(.+)\\]';
    public const RESPONSE_CODE_INDEX = 1;
    public const RESPONSE_MESSAGE_INDEX = 2;
    public const RESPONSE_SUCCESS_CODE = 'success';
    public const RESPONSE_ERROR_CODE = 'error';
    public const ADOBE_IMS_JS_SIGNIN = 'Magento_AdobeIms/js/signIn';
    public const ADOBE_IMS_SIGNIN = 'Magento_AdobeIms/signIn';
    public const ADOBE_IMS_USER_PROFILE = 'adobe_ims/user/profile';
    public const ADOBE_IMS_USER_LOGOUT = 'adobe_ims/user/logout';

    /**
     * @var ConfigInterface
     */
    private $config;

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
     * JsonHexTag Serializer Instance
     *
     * @var JsonHexTag
     */
    private $serializer;

    /**
     * SignIn constructor.
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param UserContextInterface $userContext
     * @param UserAuthorizedInterface $userAuthorized
     * @param UserProfileRepositoryInterface $userProfileRepository
     * @param JsonHexTag $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        UserContextInterface $userContext,
        UserAuthorizedInterface $userAuthorized,
        UserProfileRepositoryInterface $userProfileRepository,
        JsonHexTag $json,
        array $data = []
    ) {
        $this->config = $config;
        $this->userContext = $userContext;
        $this->userAuthorized = $userAuthorized;
        $this->userProfileRepository = $userProfileRepository;
        $this->serializer = $json;
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
            'component' => self::ADOBE_IMS_JS_SIGNIN,
            'template' => self::ADOBE_IMS_SIGNIN,
            'profileUrl' => $this->getUrl(self::ADOBE_IMS_USER_PROFILE),
            'logoutUrl' => $this->getUrl(self::ADOBE_IMS_USER_LOGOUT),
            'user' => $this->getUserData(),
            'isGlobalSignInEnabled' => false,
            'loginConfig' => [
                'url' => $this->config->getAuthUrl(),
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
