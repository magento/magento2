<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

interface FieldsetInterface
{
    /**
     * Add additional fields to fieldset
     *
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data);
}
