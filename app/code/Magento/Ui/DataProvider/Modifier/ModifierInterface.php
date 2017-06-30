<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * @api
 */
interface ModifierInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data);

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta);
}
