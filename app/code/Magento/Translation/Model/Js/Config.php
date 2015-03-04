<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Js Translation config
 */
class Config
{
    /**
     * Strategy when all js files are translated while publishing
     */
    const PUBLISHING_STRATEGY = 'publishing';

    /**
     * Strategy when dictionary is generated for dynamic translation
     */
    const DYNAMIC_STRATEGY = 'dynamic';

    /**
     * Configuration path to translation strategy
     */
    const TRANSLATE_CONFIG_PATH = 'dev/js/translate_strategy';

    /**
     * Dictionary file name
     */
    const DICTIONARY_FILE_NAME = 'i18n.json';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isPublishingMode()
    {
        return ($this->scopeConfig->getValue(self::TRANSLATE_CONFIG_PATH) == self::PUBLISHING_STRATEGY);
    }

    /**
     * @return bool
     */
    public function isDictionaryMode()
    {
        return ($this->scopeConfig->getValue(self::TRANSLATE_CONFIG_PATH) == self::DYNAMIC_STRATEGY);
    }
}
