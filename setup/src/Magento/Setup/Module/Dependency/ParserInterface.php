<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency;

/**
 * Parser Interface
 * @since 2.0.0
 */
interface ParserInterface
{
    /**
     * Parse files
     *
     * @param array $options
     * @return array
     * @since 2.0.0
     */
    public function parse(array $options);
}
