<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin\Block\Adminhtml;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdobeIms\Block\Adminhtml\SignIn;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\ConfigProviderInterface;
use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\Framework\Serialize\Serializer\JsonHexTag;

/**
 * Plugin to get authentication component configuration if Admin Adobe IMS is enabled
 */
class SignInPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var UserAuthorizedInterface
     */
    private UserAuthorizedInterface $userAuthorized;

    /**
     * JsonHexTag Serializer Instance
     *
     * @var JsonHexTag
     */
    private JsonHexTag $serializer;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param ImsConfig $adminAdobeImsConfig
     * @param Auth $auth
     * @param UserAuthorizedInterface $userAuthorized
     * @param JsonHexTag $serializer
     * @param ConfigInterface $config
     */
    public function __construct(
        ImsConfig $adminAdobeImsConfig,
        Auth $auth,
        UserAuthorizedInterface $userAuthorized,
        JsonHexTag $serializer,
        ConfigInterface $config
    ) {
        $this->adminAdobeImsConfig = $adminAdobeImsConfig;
        $this->auth = $auth;
        $this->userAuthorized = $userAuthorized;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * Get authentication component configuration if Admin Adobe IMS is enabled
     *
     * @param SignIn $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetComponentJsonConfig(SignIn $subject, callable $proceed): string
    {
        if (!$this->adminAdobeImsConfig->enabled()) {
            return $proceed();
        }

        return $this->serializer->serialize(
            array_replace_recursive(
                $this->getDefaultComponentConfig($subject),
                ...$this->getExtendedComponentConfig($subject)
            )
        );
    }

    /**
     * Get default UI component configuration
     *
     * @param SignIn $subject
     * @return array
     */
    private function getDefaultComponentConfig(SignIn $subject): array
    {
        return [
            'component' => SignIn::ADOBE_IMS_JS_SIGNIN,
            'template' => SignIn::ADOBE_IMS_SIGNIN,
            'profileUrl' => $subject->getUrl(SignIn::ADOBE_IMS_USER_PROFILE),
            'logoutUrl' => $subject->getUrl(SignIn::ADOBE_IMS_USER_LOGOUT),
            'user' => $this->getUserData(),
            'isGlobalSignInEnabled' => true,
            'loginConfig' => [
                'url' => $this->config->getAuthUrl(),
                'callbackParsingParams' => [
                    'regexpPattern' => SignIn::RESPONSE_REGEXP_PATTERN,
                    'codeIndex' => SignIn::RESPONSE_CODE_INDEX,
                    'messageIndex' => SignIn::RESPONSE_MESSAGE_INDEX,
                    'successCode' => SignIn::RESPONSE_SUCCESS_CODE,
                    'errorCode' => SignIn::RESPONSE_ERROR_CODE
                ]
            ]
        ];
    }

    /**
     * Get UI component configuration extension specified in layout configuration for block instance
     *
     * @param SignIn $subject
     * @return array
     */
    private function getExtendedComponentConfig(SignIn $subject): array
    {
        $configProviders = $subject->getData(SignIn::DATA_ARGUMENT_KEY_CONFIG_PROVIDERS);
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

        $user = $this->auth->getUser();

        return [
            'isAuthorized' => true,
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'image' => ''
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
