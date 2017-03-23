<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
