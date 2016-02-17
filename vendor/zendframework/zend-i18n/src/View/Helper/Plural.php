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
use Zend\I18n\Translator\Plural\Rule as PluralRule;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for rendering text based on a count number (like the I18n plural translation helper, but when translation
 * is not needed).
 *
 * Please note that we did not write any hard-coded rules for languages, as languages can evolve, we preferred to
 * let the developer define the rules himself, instead of potentially break applications if we change rules in the
 * future.
 *
 * However, you can find most of the up-to-date plural rules for most languages in those links:
 *      - http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
 *      - https://developer.mozilla.org/en-US/docs/Localization_and_Plurals
 */
class Plural extends AbstractHelper
{
    /**
     * Plural rule to use
     *
     * @var PluralRule
     */
    protected $rule;

    /**
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present
     */
    public function __construct()
    {
        if (!extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(sprintf(
                '%s component requires the intl PHP extension',
                __NAMESPACE__
            ));
        }
    }

    /**
     * Given an array of strings, a number and, if wanted, an optional locale (the default one is used
     * otherwise), this picks the right string according to plural rules of the locale
     *
     * @param  array|string $strings
     * @param  int          $number
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function __invoke($strings, $number)
    {
        if (null === $this->getPluralRule()) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No plural rule was set'
            ));
        }

        if (!is_array($strings)) {
            $strings = (array) $strings;
        }

        $pluralIndex = $this->getPluralRule()->evaluate($number);

        return $strings[$pluralIndex];
    }

    /**
     * Set the plural rule to use
     *
     * @param  PluralRule|string $pluralRule
     * @return Plural
     */
    public function setPluralRule($pluralRule)
    {
        if (!$pluralRule instanceof PluralRule) {
            $pluralRule = PluralRule::fromString($pluralRule);
        }

        $this->rule = $pluralRule;

        return $this;
    }

    /**
     * Get the plural rule to  use
     *
     * @return PluralRule
     */
    public function getPluralRule()
    {
        return $this->rule;
    }
}
