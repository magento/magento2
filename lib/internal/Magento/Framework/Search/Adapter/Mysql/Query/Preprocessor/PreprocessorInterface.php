<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Query\Preprocessor;

interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
