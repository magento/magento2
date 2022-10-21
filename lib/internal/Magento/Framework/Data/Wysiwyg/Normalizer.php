<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Wysiwyg;

/**
 * Normalize widget content in Wysiwyg editor
 */
class Normalizer
{
    public const WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP = [
        '{' => '^[',
        '}' => '^]',
        '"' => '`',
        '\\' => '|',
        '<' => '^(',
        '>' => '^)'
    ];

    /**
     * Replace the reserved characters in the content
     *
     * @param string $content
     * @return string
     */
    public function replaceReservedCharacters($content)
    {
        return $content !== null ? str_replace(
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            array_values(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            $content
        ) : '';
    }

    /**
     * Restore the reserved characters in the content
     *
     * @param string $content
     * @return string
     */
    public function restoreReservedCharacters($content)
    {
        return $content !== null ? str_replace(
            array_values(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            $content
        ) : '';
    }
}
