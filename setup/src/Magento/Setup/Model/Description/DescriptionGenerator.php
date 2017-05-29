<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description;

/**
 * Generate random description based on configuration
 */
class DescriptionGenerator
{
    /**
     * @var \Magento\Setup\Model\Description\DescriptionParagraphGenerator
     */
    private $paragraphGenerator;

    /**
     * @var \Magento\Setup\Model\Description\MixinManager
     */
    private $mixinManager;

    /**
     * @var array
     */
    private $descriptionConfig;

    /**
     * @param \Magento\Setup\Model\Description\DescriptionParagraphGenerator $paragraphGenerator
     * @param \Magento\Setup\Model\Description\MixinManager $mixinManager
     * @param array $descriptionConfig
     */
    public function __construct(
        \Magento\Setup\Model\Description\DescriptionParagraphGenerator $paragraphGenerator,
        \Magento\Setup\Model\Description\MixinManager $mixinManager,
        array $descriptionConfig
    ) {
        $this->paragraphGenerator = $paragraphGenerator;
        $this->mixinManager = $mixinManager;
        $this->descriptionConfig = $descriptionConfig;
    }

    /**
     * Generate description and apply mixin to it
     *
     * @return string
     */
    public function generate()
    {
        $description = $this->generateRawDescription();

        if (isset($this->descriptionConfig['mixin'])) {
            $description = $this->mixinManager->apply($description, $this->descriptionConfig['mixin']['tags']);
        }

        return $description;
    }

    /**
     * Generate raw description without mixin
     *
     * @return string
     */
    private function generateRawDescription()
    {
        $paragraphsCount = mt_rand(
            $this->descriptionConfig['paragraphs']['count-min'],
            $this->descriptionConfig['paragraphs']['count-max']
        );
        $descriptionParagraphs = '';

        while ($paragraphsCount) {
            $descriptionParagraphs .= $this->paragraphGenerator->generate();
            $descriptionParagraphs .= PHP_EOL;
            $paragraphsCount--;
        }

        $descriptionParagraphs = rtrim($descriptionParagraphs);

        return $descriptionParagraphs;
    }
}
