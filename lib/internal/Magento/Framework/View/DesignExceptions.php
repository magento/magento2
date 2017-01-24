<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class DesignExceptions
 */
class DesignExceptions
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Exception config path
     *
     * @var string
     */
    protected $exceptionConfigPath;

    /**
     * Scope Type
     *
     * @var string
     */
    protected $scopeType;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * DesignExceptions constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $exceptionConfigPath
     * @param string $scopeType
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $exceptionConfigPath,
        $scopeType,
        Json $serializer = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->exceptionConfigPath = $exceptionConfigPath;
        $this->scopeType = $scopeType;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Get theme that should be applied for current user-agent according to design exceptions configuration
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return string|bool
     */
    public function getThemeByRequest(\Magento\Framework\App\Request\Http $request)
    {
        $userAgent = $request->getServer('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            return false;
        }
        $expressions = $this->scopeConfig->getValue(
            $this->exceptionConfigPath,
            $this->scopeType
        );
        if (!$expressions) {
            return false;
        }
        $expressions = $this->serializer->unserialize($expressions);
        foreach ($expressions as $rule) {
            if (preg_match($rule['regexp'], $userAgent)) {
                return $rule['value'];
            }
        }
        return false;
    }
}
