<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Js;

use Magento\Framework\Translate\Js\Config as FrameworkJsConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Js Translation config
 */
class Config extends FrameworkJsConfig
{
    /**
     * Both translation strategies are disabled
     */
    const NO_TRANSLATION = 'none';

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
    public function __construct(ScopeConfigInterface $scopeConfig, array $patterns)
    {
        $this->scopeConfig = $scopeConfig;
        $this->patterns = $patterns;
        parent::__construct(
            $this->scopeConfig->getValue(self::XML_PATH_STRATEGY) == self::DICTIONARY_STRATEGY,
            self::DICTIONARY_FILE_NAME
        );
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
    public function dictionaryEnabled()
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
