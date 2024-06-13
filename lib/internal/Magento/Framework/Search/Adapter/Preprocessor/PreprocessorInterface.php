<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Adapter\Preprocessor;

/**
 * Interface \Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface
 *
 * @api
 */
interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
