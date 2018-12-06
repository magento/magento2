<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Unserialize\SecureUnserializer;
use Psr\Log\LoggerInterface;

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
     * @var SecureUnserializer
     */
    private $secureUnserializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $exceptionConfigPath
     * @param string $scopeType
     * @param SecureUnserializer|null $secureUnserializer
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $exceptionConfigPath,
        $scopeType,
        SecureUnserializer $secureUnserializer = null,
        LoggerInterface $logger = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->exceptionConfigPath = $exceptionConfigPath;
        $this->scopeType = $scopeType;
        $this->secureUnserializer = $secureUnserializer ?:
            ObjectManager::getInstance()->create(SecureUnserializer::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->create(LoggerInterface::class);
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

        try {
            $expressions = $this->secureUnserializer->unserialize($expressions);
        } catch (\InvalidArgumentException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }

        foreach ($expressions as $rule) {
            if (preg_match($rule['regexp'], $userAgent)) {
                return $rule['value'];
            }
        }

        return false;
    }
}
