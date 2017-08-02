<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;

/**
 * Php parser adapter
 * @since 2.0.0
 */
class Php extends AbstractAdapter
{
    /**
     * Phrase collector
     *
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
     * @since 2.0.0
     */
    protected $_phraseCollector;

    /**
     * Adapter construct
     *
     * @param \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector $phraseCollector
     * @since 2.0.0
     */
    public function __construct(PhraseCollector $phraseCollector)
    {
        $this->_phraseCollector = $phraseCollector;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _parse()
    {
        $this->_phraseCollector->setIncludeObjects();
        $this->_phraseCollector->parse($this->_file);

        foreach ($this->_phraseCollector->getPhrases() as $phrase) {
            $this->_addPhrase($phrase['phrase'], $phrase['line']);
        }
    }
}
