<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Preprocessor;

/**
 * Interface \Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface
 *
 * @since 2.1.0
 */
interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     * @since 2.1.0
     */
    public function process($query);
}
