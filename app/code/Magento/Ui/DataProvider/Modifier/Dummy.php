<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * Class Dummy
 */
class Dummy implements ModifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
