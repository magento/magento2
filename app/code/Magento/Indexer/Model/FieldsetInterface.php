<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

interface FieldsetInterface
{
    /**
     * @param [] $data
     * @return void
     */
    public function proccess($data);
}
