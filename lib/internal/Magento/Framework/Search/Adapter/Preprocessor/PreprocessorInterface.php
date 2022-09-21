<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
