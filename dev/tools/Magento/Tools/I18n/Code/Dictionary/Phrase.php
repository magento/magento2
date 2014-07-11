<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Dictionary;

/**
 *  Phrase
 */
class Phrase
{
    /**
     * Single quote that enclose the phrase
     *
     * @var string
     */
    const QUOTE_SINGLE = "'";

    /**
     * Double quote that enclose the phrase
     *
     * @var string
     */
    const QUOTE_DOUBLE = '"';

    /**
     * Phrase
     *
     * @var string
     */
    private $_phrase;

    /**
     * Translation
     *
     * @var string
     */
    private $_translation;

    /**
     * Context type
     *
     * @var string
     */
    private $_contextType;

    /**
     * Context value
     *
     * @var array
     */
    private $_contextValue = array();

    /**
     * Quote type that enclose the phrase, single or double
     *
     * @var string
     */
    private $_quote;

    /**
     * Phrase construct
     *
     * @param string $phrase
     * @param string $translation
     * @param string|null $contextType
     * @param string|array|null $contextValue
     * @param string|null $quote
     */
    public function __construct($phrase, $translation, $contextType = null, $contextValue = null, $quote = null)
    {
        $this->setPhrase($phrase);
        $this->setTranslation($translation);
        $this->setContextType($contextType);
        $this->setContextValue($contextValue);
        $this->setQuote($quote);
    }

    /**
     * Set phrase
     *
     * @param string $phrase
     * @return void
     * @throws \DomainException
     */
    public function setPhrase($phrase)
    {
        if (!$phrase) {
            throw new \DomainException('Missed phrase');
        }
        $this->_phrase = $phrase;
    }

    /**
     * Get quote type
     *
     * @return string
     */
    public function getPhrase()
    {
        return $this->_phrase;
    }

    /**
     * Set quote type
     *
     * @param string $quote
     * @return void
     */
    public function setQuote($quote)
    {
        if (in_array($quote, array(self::QUOTE_SINGLE, self::QUOTE_DOUBLE))) {
            $this->_quote = $quote;
        }
    }

    /**
     * Get phrase
     *
     * @return string
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Set translation
     *
     * @param string $translation
     * @return void
     * @throws \DomainException
     */
    public function setTranslation($translation)
    {
        if (!$translation) {
            throw new \DomainException('Missed translation');
        }
        $this->_translation = $translation;
    }

    /**
     * Get translation
     *
     * @return string
     */
    public function getTranslation()
    {
        return $this->_translation;
    }

    /**
     * Set context type
     *
     * @param string $contextType
     * @return void
     */
    public function setContextType($contextType)
    {
        $this->_contextType = $contextType;
    }

    /**
     * Get context type
     *
     * @return string
     */
    public function getContextType()
    {
        return $this->_contextType;
    }

    /**
     * Add context value
     *
     * @param string $contextValue
     * @return void
     * @throws \DomainException
     */
    public function addContextValue($contextValue)
    {
        if (empty($contextValue)) {
            throw new \DomainException('Context value is empty');
        }
        if (!in_array($contextValue, $this->_contextValue)) {
            $this->_contextValue[] = $contextValue;
        }
    }

    /**
     * Set context type
     *
     * @param string $contextValue
     * @return void
     * @throws \DomainException
     */
    public function setContextValue($contextValue)
    {
        if (is_string($contextValue)) {
            $contextValue = explode(',', $contextValue);
        } elseif (null == $contextValue) {
            $contextValue = array();
        } elseif (!is_array($contextValue)) {
            throw new \DomainException('Wrong context type');
        }
        $this->_contextValue = $contextValue;
    }

    /**
     * Get context value
     *
     * @return array
     */
    public function getContextValue()
    {
        return $this->_contextValue;
    }

    /**
     * Get context value as string
     *
     * @param string $separator
     * @return string
     */
    public function getContextValueAsString($separator = ',')
    {
        return implode($separator, $this->_contextValue);
    }

    /**
     * Get VO identifier key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getPhrase() . '::' . $this->getContextType();
    }
}
