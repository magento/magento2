<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n;

/**
 * Parser Interface
 */
interface ParserInterface
{
    /**
     * Parse by parser options
     *
     * @param array $parseOptions
     * @return array
     */
    public function parse(array $parseOptions);

    /**
     * Get parsed phrases
     *
     * @return array
     */
    public function getPhrases();
}
