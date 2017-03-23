<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

interface AttributesListInterface
{
    /**
     * Retrieve list of attributes
     *
     * @param array $ids
     * @return array
     */
    public function getAttributes($ids);
}
