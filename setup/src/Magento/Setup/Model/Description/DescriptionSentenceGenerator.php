<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description;

/**
 * Generate random sentence for description based on configuration
 * @since 2.2.0
 */
class DescriptionSentenceGenerator
{
    /**
     * @var \Magento\Setup\Model\Dictionary
     * @since 2.2.0
     */
    private $dictionary;

    /**
     * @var array
     * @since 2.2.0
     */
    private $sentenceConfig;

    /**
     * @param \Magento\Setup\Model\Dictionary $dictionary
     * @param array $sentenceConfig
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Setup\Model\Dictionary $dictionary,
        array $sentenceConfig
    ) {
        $this->dictionary = $dictionary;
        $this->sentenceConfig = $sentenceConfig;
    }

    /**
     * Generate sentence for description
     *
     * @return string
     * @since 2.2.0
     */
    public function generate()
    {
        $sentenceWordsCount = mt_rand(
            $this->sentenceConfig['words']['count-min'],
            $this->sentenceConfig['words']['count-max']
        );
        $sentence = '';

        while ($sentenceWordsCount) {
            $sentence .= $this->dictionary->getRandWord();
            $sentence .= ' ';
            $sentenceWordsCount--;
        }

        return ucfirst(rtrim($sentence)) . '.';
    }
}
