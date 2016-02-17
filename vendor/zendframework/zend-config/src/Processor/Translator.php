<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Processor;

use Zend\Config\Config;
use Zend\Config\Exception;
use Zend\I18n\Translator\Translator as ZendTranslator;

class Translator implements ProcessorInterface
{
    /**
     * @var ZendTranslator
     */
    protected $translator;

    /**
     * @var string|null
     */
    protected $locale = null;

    /**
     * @var string
     */
    protected $textDomain = 'default';

    /**
     * Translator uses the supplied Zend\I18n\Translator\Translator to find
     * and translate language strings in config.
     *
     * @param  ZendTranslator $translator
     * @param  string $textDomain
     * @param  string|null $locale
     */
    public function __construct(ZendTranslator $translator, $textDomain = 'default', $locale = null)
    {
        $this->setTranslator($translator);
        $this->setTextDomain($textDomain);
        $this->setLocale($locale);
    }

    /**
     * @param  ZendTranslator $translator
     * @return Translator
     */
    public function setTranslator(ZendTranslator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * @return ZendTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param  string|null $locale
     * @return Translator
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param  string $textDomain
     * @return Translator
     */
    public function setTextDomain($textDomain)
    {
        $this->textDomain = $textDomain;
        return $this;
    }

    /**
     * @return string
     */
    public function getTextDomain()
    {
        return $this->textDomain;
    }

    /**
     * Process
     *
     * @param  Config $config
     * @return Config
     * @throws Exception\InvalidArgumentException
     */
    public function process(Config $config)
    {
        if ($config->isReadOnly()) {
            throw new Exception\InvalidArgumentException('Cannot process config because it is read-only');
        }

        /**
         * Walk through config and replace values
         */
        foreach ($config as $key => $val) {
            if ($val instanceof Config) {
                $this->process($val);
            } else {
                $config->{$key} = $this->translator->translate($val, $this->textDomain, $this->locale);
            }
        }

        return $config;
    }

    /**
     * Process a single value
     *
     * @param $value
     * @return string
     */
    public function processValue($value)
    {
        return $this->translator->translate($value, $this->textDomain, $this->locale);
    }
}
