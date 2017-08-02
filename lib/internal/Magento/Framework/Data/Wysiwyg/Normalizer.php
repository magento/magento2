<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Wysiwyg;

/**
 * Normalize widget content in Wysiwyg editor
 * @since 2.2.0
 */
class Normalizer
{

    const WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP = [
        '{' => '^[',
        '}' => '^]',
        '"' => '`',
        '\\' => '|',
    ];

    /**
     * Replace the reserved characters in the content
     *
     * @param string $content
     * @return string
     * @since 2.2.0
     */
    public function replaceReservedCharacters($content)
    {
        return str_replace(
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            array_values(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            $content
        );
    }

    /**
     * Restore the reserved characters in the content
     *
     * @param string $content
     * @return string
     * @since 2.2.0
     */
    public function restoreReservedCharacters($content)
    {
        return str_replace(
            array_values(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARACTERS_REPLACEMENT_MAP),
            $content
        );
    }
}
