<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n;

use Magento\Tools\I18n\Dictionary\Phrase;

/**
 *  Dictionary
 */
class Dictionary
{
    /**
     * Phrases
     *
     * @var array
     */
    private $_phrases = [];

    /**
     * List of phrases where array key is vo key
     *
     * @var array
     */
    private $_phrasesByKey = [];

    /**
     * Add phrase to pack container
     *
     * @param \Magento\Tools\I18n\Dictionary\Phrase $phrase
     * @return void
     */
    public function addPhrase(Phrase $phrase)
    {
        $this->_phrases[] = $phrase;
        $this->_phrasesByKey[$phrase->getKey()][] = $phrase;
    }

    /**
     * Get phrases
     *
     * @return \Magento\Tools\I18n\Dictionary\Phrase[]
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }

    /**
     * Get duplicates in container
     *
     * @return array
     */
    public function getDuplicates()
    {
        return array_values(
            array_filter(
                $this->_phrasesByKey,
                function ($phrases) {
                    return count($phrases) > 1;
                }
            )
        );
    }
}
