<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

use Magento\Setup\Module\I18n\Dictionary\Phrase;

/**
 *  Dictionary
 * @since 2.0.0
 */
class Dictionary
{
    /**
     * Phrases
     *
     * @var array
     * @since 2.0.0
     */
    private $_phrases = [];

    /**
     * List of phrases where array key is vo key
     *
     * @var array
     * @since 2.0.0
     */
    private $_phrasesByKey = [];

    /**
     * Add phrase to pack container
     *
     * @param Phrase $phrase
     * @return void
     * @since 2.0.0
     */
    public function addPhrase(Phrase $phrase)
    {
        $this->_phrases[] = $phrase;
        $this->_phrasesByKey[$phrase->getKey()][] = $phrase;
    }

    /**
     * Get phrases
     *
     * @return Phrase[]
     * @since 2.0.0
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }

    /**
     * Get duplicates in container
     *
     * @return array
     * @since 2.0.0
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
