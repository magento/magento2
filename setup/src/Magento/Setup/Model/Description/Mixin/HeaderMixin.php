<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Add header html tag to description
 */
class HeaderMixin implements DescriptionMixinInterface
{
    /**
     * Add <h1> header with text before each new line (\r\n)
     *
     * @param string $text
     * @return string
     */
    public function apply($text)
    {
        $paragraphs = explode(PHP_EOL, trim($text));
        $magicLengthNumber = 10;

        foreach ($paragraphs as &$paragraph) {
            $rawText = trim(strip_tags($paragraph));
            if (empty($rawText)) {
                continue;
            }

            $headerText = substr($rawText, 0, strpos($rawText, ' ', $magicLengthNumber));
            $paragraph = '<h1>' . $headerText . '</h1>' . PHP_EOL . $paragraph;
        }

        return implode(PHP_EOL, $paragraphs);
    }
}
