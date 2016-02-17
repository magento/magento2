<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator\Loader;

use Zend\Config\Reader\Ini as IniReader;
use Zend\I18n\Exception;
use Zend\I18n\Translator\Plural\Rule as PluralRule;
use Zend\I18n\Translator\TextDomain;

/**
 * PHP INI format loader.
 */
class Ini extends AbstractFileLoader
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

        $messages           = array();
        $iniReader          = new IniReader();
        $messagesNamespaced = $iniReader->fromFile($fromIncludePath);

        $list = $messagesNamespaced;
        if (isset($messagesNamespaced['translation'])) {
            $list = $messagesNamespaced['translation'];
        }

        foreach ($list as $message) {
            if (!is_array($message) || count($message) < 2) {
                throw new Exception\InvalidArgumentException(
                    'Each INI row must be an array with message and translation'
                );
            }
            if (isset($message['message']) && isset($message['translation'])) {
                $messages[$message['message']] = $message['translation'];
                continue;
            }
            $messages[array_shift($message)] = array_shift($message);
        }

        if (!is_array($messages)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected an array, but received %s',
                gettype($messages)
            ));
        }

        $textDomain = new TextDomain($messages);

        if (array_key_exists('plural', $messagesNamespaced)
            && isset($messagesNamespaced['plural']['plural_forms'])
        ) {
            $textDomain->setPluralRule(
                PluralRule::fromString($messagesNamespaced['plural']['plural_forms'])
            );
        }

        return $textDomain;
    }
}
