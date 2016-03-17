<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * Class ModifierInterface
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
