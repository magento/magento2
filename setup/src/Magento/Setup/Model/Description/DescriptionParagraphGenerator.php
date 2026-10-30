<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model\Description;

/**
 * Generate random paragraph for description based on configuration
 */
class DescriptionParagraphGenerator
{
    /**
     * @var \Magento\Setup\Model\Description\DescriptionSentenceGenerator
     */
    private $sentenceGenerator;

    /**
     * @var array
     */
    private $paragraphConfig;

    /**
     * @param \Magento\Setup\Model\Description\DescriptionSentenceGenerator $sentenceGenerator
     * @param array $paragraphConfig
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
     */
    public function generate()
    {
        // mt_rand() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
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
