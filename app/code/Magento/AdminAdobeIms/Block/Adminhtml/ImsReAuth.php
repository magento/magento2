<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Block\Adminhtml;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeImsApi\Api\ConfigProviderInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\JsonHexTag;

/**
 * Provides required data for the Adobe service authentication component
 *
 * @api
 */
class ImsReAuth extends Template
{
    private const DATA_ARGUMENT_KEY_CONFIG_PROVIDERS = 'configProviders';
    private const RESPONSE_REGEXP_PATTERN = 'auth\\[code=(success|error);message=(.+)\\]';
    private const RESPONSE_CODE_INDEX = 1;
    private const RESPONSE_MESSAGE_INDEX = 2;
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';
    private const ADOBE_IMS_JS_REAUTH = 'Magento_AdminAdobeIms/js/adobe-ims-reauth';
    private const ADOBE_IMS_REAUTH = 'Magento_AdminAdobeIms/adobe-ims-reauth';

    /**
     * @var ImsConfig
     */
    private $adminImsConfig;

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
     * @param ImsConfig $adminImsConfig
     * @param JsonHexTag $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        ImsConfig $adminImsConfig,
        JsonHexTag $json,
        array $data = []
    ) {
        $this->adminImsConfig = $adminImsConfig;
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
            'component' => self::ADOBE_IMS_JS_REAUTH,
            'template' => self::ADOBE_IMS_REAUTH,
            'loginConfig' => [
                'url' => $this->adminImsConfig->getAdminAdobeImsReAuthUrl(),
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
}
