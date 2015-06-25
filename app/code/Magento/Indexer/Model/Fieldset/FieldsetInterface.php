<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Fieldset;

use Magento\Indexer\Model\HandlerInterface;
use Magento\Indexer\Model\Source\DataInterface;

interface FieldsetInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function addDynamicData(array $data);
}
