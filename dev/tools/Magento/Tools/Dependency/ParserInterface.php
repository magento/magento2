<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency;

/**
 * Parser Interface
 */
interface ParserInterface
{
    /**
     * Parse files
     *
     * @param array $options
     * @return array
     */
    public function parse(array $options);
}
