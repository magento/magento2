<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser;

/**
 * Adapter Interface
 * @since 2.0.0
 */
interface AdapterInterface
{
    /**
     * Parse file
     *
     * @param string $file
     * @return array
     * @since 2.0.0
     */
    public function parse($file);

    /**
     * Get parsed phrases
     *
     * @return array
     * @since 2.0.0
     */
    public function getPhrases();
}
