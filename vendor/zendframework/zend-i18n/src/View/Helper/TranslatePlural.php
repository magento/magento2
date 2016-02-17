<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\View\Helper;

use Zend\I18n\Exception;

/**
 * View helper for translating plural messages.
 */
class TranslatePlural extends AbstractTranslatorHelper
{
    /**
     * Translate a plural message
     *
     * @param  string  $singular
     * @param  string  $plural
     * @param  int $number
     * @param  string  $textDomain
     * @param  string  $locale
     * @throws Exception\RuntimeException
     * @return string
     */
    public function __invoke(
        $singular,
        $plural,
        $number,
        $textDomain = null,
        $locale = null
    ) {
        $translator = $this->getTranslator();
        if (null === $translator) {
            throw new Exception\RuntimeException('Translator has not been set');
        }
        if (null === $textDomain) {
            $textDomain = $this->getTranslatorTextDomain();
        }

        return $translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }
}
