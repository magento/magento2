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
    const EMBEDDED_STRATEGY = 'embedded';

    /**
     * Strategy when dictionary is generated for dynamic translation
     */
    const DICTIONARY_STRATEGY = 'dictionary';

    /**
     * Configuration path to translation strategy
     */
    const XML_PATH_STRATEGY = 'dev/js/translate_strategy';

    /**
     * Dictionary file name
     */
    const DICTIONARY_FILE_NAME = 'js-translation.json';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Patterns to match strings for translation
     *
     * @var string[]
     */
    protected $patterns;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string[] $patterns
     */
    public function __construct(ScopeConfigInterface $scopeConfig, $patterns)
    {
        $this->scopeConfig = $scopeConfig;
        $this->patterns = $patterns;
    }

    /**
     * Is Embedded Strategy selected
     *
     * @return bool
     */
    public function isEmbeddedStrategy()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_STRATEGY) == self::EMBEDDED_STRATEGY);
    }

    /**
     * Is Dictionary Strategy selected
     *
     * @return bool
     */
    public function isDictionaryStrategy()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_STRATEGY) == self::DICTIONARY_STRATEGY);
    }

    /**
     * Retrieve translation patterns
     *
     * @return string[]
     */
    public function getPatterns()
    {
        return $this->patterns;
    }
}
