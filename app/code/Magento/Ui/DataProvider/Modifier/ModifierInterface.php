<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * @api
 * @since 100.1.0
 */
interface ModifierInterface
{
    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data);

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta);
}
