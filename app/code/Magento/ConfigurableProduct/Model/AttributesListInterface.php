<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

/**
 * @api
 * @since 2.0.0
 */
interface AttributesListInterface
{
    /**
     * Retrieve list of attributes
     *
     * @param array $ids
     * @return array
     * @since 2.0.0
     */
    public function getAttributes($ids);
}
