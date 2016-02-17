<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator\Loader;

use Zend\I18n\Exception;
use Zend\I18n\Translator\Plural\Rule as PluralRule;
use Zend\I18n\Translator\TextDomain;

/**
 * PHP Memory array loader.
 */
class PhpMemoryArray implements RemoteLoaderInterface
{
    /**
     * @var array
     */
    protected $messages;

    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Load translations from a remote source.
     *
     * @param  string $locale
     * @param  string $textDomain
     *
     * @throws \Zend\I18n\Exception\InvalidArgumentException
     * @return \Zend\I18n\Translator\TextDomain|null
     */
    public function load($locale, $textDomain)
    {
        if (!is_array($this->messages)) {
            throw new Exception\InvalidArgumentException(
                sprintf('Expected an array, but received %s', gettype($this->messages))
            );
        }

        if (!isset($this->messages[$textDomain])) {
            throw new Exception\InvalidArgumentException(
                sprintf('Expected textdomain "%s" to be an array, but it is not set', $textDomain)
            );
        }

        if (!isset($this->messages[$textDomain][$locale])) {
            throw new Exception\InvalidArgumentException(
                sprintf('Expected locale "%s" to be an array, but it is not set', $locale)
            );
        }

        $textDomain = new TextDomain($this->messages[$textDomain][$locale]);

        if (array_key_exists('', $textDomain)) {
            if (isset($textDomain['']['plural_forms'])) {
                $textDomain->setPluralRule(
                    PluralRule::fromString($textDomain['']['plural_forms'])
                );
            }

            unset($textDomain['']);
        }

        return $textDomain;
    }
}
