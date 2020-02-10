<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Add bold html tag in random position to description
 */
class BoldMixin implements DescriptionMixinInterface
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector
     */
    private $randomWordSelector;

    /**
     * @var \Magento\Setup\Model\Description\Mixin\Helper\WordWrapper
     */
    private $wordWrapper;

    /**
     * @param \Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector $randomWordSelector
     * @param \Magento\Setup\Model\Description\Mixin\Helper\WordWrapper $wordWrapper
     */
    public function __construct(
        \Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector $randomWordSelector,
        \Magento\Setup\Model\Description\Mixin\Helper\WordWrapper $wordWrapper
    ) {
        $this->randomWordSelector = $randomWordSelector;
        $this->wordWrapper = $wordWrapper;
    }

    /**
     * Add <b></b> tag to text at random positions
     *
     * @param string $text
     * @return string
     */
    public function apply($text)
    {
        if (empty(strip_tags(trim($text)))) {
            return $text;
        }

        $rawText = strip_tags($text);

        return $this->wordWrapper->wrapWords(
            $text,
            $this->randomWordSelector->getRandomWords($rawText, mt_rand(5, 8)),
            '<b>%s</b>'
        );
    }
}
