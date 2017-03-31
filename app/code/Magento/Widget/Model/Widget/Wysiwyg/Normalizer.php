<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget\Wysiwyg;

/**
 * Normalize widget content in Wysiwyg editor
 */
class Normalizer
{

    const WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP = [
        '{' => '[',
        '}' => ']',
        '"' => '`',
        '\\' => '|',
    ];

    /**
     * Replace the reserved characters in the content
     *
     * @param string $content
     * @return string
     */
    public function replaceReservedCharaters($content)
    {
        return str_replace(
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_values(Normalizer::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $content
        );
    }

    /**
     * Restore the reserved characters in the content
     *
     * @param string $content
     * @return string
     */
    public function restoreReservedCharaters($content)
    {
        return str_replace(
            array_values(Normalizer::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            array_keys(Normalizer::WYSIWYG_RESERVED_CHARCTERS_REPLACEMENT_MAP),
            $content
        );
    }
}
