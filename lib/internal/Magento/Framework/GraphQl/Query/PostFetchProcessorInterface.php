<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

/**
 * Processor or processors to re-format and add additional data outside of the scope of the query's fetch.
 */
interface PostFetchProcessorInterface
{
    /**
     * Process data by formatting and add any necessary additional attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData) : array;
}
