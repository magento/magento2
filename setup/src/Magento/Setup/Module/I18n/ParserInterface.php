<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

/**
 * Parser Interface
 * @since 2.0.0
 */
interface ParserInterface
{
    /**
     * Parse by parser options
     *
     * @param array $parseOptions
     * @return array
     * @since 2.0.0
     */
    public function parse(array $parseOptions);

    /**
     * Get parsed phrases
     *
     * @return array
     * @since 2.0.0
     */
    public function getPhrases();
}
