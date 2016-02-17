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
 * PHP array loader.
 */
class PhpArray extends AbstractFileLoader
{
    /**
     * load(): defined by FileLoaderInterface.
     *
     * @see    FileLoaderInterface::load()
     * @param  string $locale
     * @param  string $filename
     * @return TextDomain|null
     * @throws Exception\InvalidArgumentException
     */
    public function load($locale, $filename)
    {
        $resolvedIncludePath = stream_resolve_include_path($filename);
        $fromIncludePath = ($resolvedIncludePath !== false) ? $resolvedIncludePath : $filename;
        if (!$fromIncludePath || !is_file($fromIncludePath) || !is_readable($fromIncludePath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Could not find or open file %s for reading',
                $filename
            ));
        }

        $messages = include $fromIncludePath;

        if (!is_array($messages)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected an array, but received %s',
                gettype($messages)
            ));
        }

        $textDomain = new TextDomain($messages);

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
