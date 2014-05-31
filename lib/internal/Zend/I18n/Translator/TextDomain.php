<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_I18n
 */

namespace Zend\I18n\Translator;

use ArrayObject;
use Zend\I18n\Translator\Plural\Rule as PluralRule;

/**
 * Text domain.
 *
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage Translator
 */
class TextDomain extends ArrayObject
{
    /**
     * Plural rule.
     *
     * @var PluralRule
     */
    protected $pluralRule;

    /**
     * Set the plural rule
     *
     * @param  PluralRule $rule
     * @return TextDomain
     */
    public function setPluralRule(PluralRule $rule)
    {
        $this->pluralRule = $rule;
        return $this;
    }

    /**
     * Get the plural rule.
     *
     * Lazy loads a default rule if none already registered
     *
     * @return PluralRule
     */
    public function getPluralRule()
    {
        if ($this->pluralRule === null) {
            $this->setPluralRule(PluralRule::fromString('nplurals=2; plural=n==1'));
        }

        return $this->pluralRule;
    }
}
