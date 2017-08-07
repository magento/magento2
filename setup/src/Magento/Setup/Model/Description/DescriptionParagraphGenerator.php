<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description;

/**
 * Generate random paragraph for description based on configuration
 * @since 2.2.0
 */
class DescriptionParagraphGenerator
{
    /**
     * @var \Magento\Setup\Model\Description\DescriptionSentenceGenerator
     * @since 2.2.0
     */
    private $sentenceGenerator;

    /**
     * @var array
     * @since 2.2.0
     */
    private $paragraphConfig;

    /**
     * @param \Magento\Setup\Model\Description\DescriptionSentenceGenerator $sentenceGenerator
     * @param array $paragraphConfig
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Setup\Model\Description\DescriptionSentenceGenerator $sentenceGenerator,
        array $paragraphConfig
    ) {
        $this->sentenceGenerator = $sentenceGenerator;
        $this->paragraphConfig = $paragraphConfig;
    }

    /**
     * Generate paragraph for description
     *
     * @return string
     * @since 2.2.0
     */
    public function generate()
    {
        $sentencesCount = mt_rand(
            $this->paragraphConfig['sentences']['count-min'],
            $this->paragraphConfig['sentences']['count-max']
        );
        $sentences = '';

        while ($sentencesCount) {
            $sentences .= $this->sentenceGenerator->generate();
            $sentences .= ' ';
            $sentencesCount--;
        }

        $sentences = rtrim($sentences);

        return $sentences;
    }
}
