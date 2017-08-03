<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * @api Implement custom Fieldset
 * @since 2.0.0
 */
interface FieldsetInterface
{
    /**
     * Add additional fields to fieldset
     *
     * @param array $data
     * @return array
     * @since 2.0.0
     */
    public function addDynamicData(array $data);
}
