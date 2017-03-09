<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Source;

use Magento\Framework\Exception\NotFoundException;

interface DataInterface
{
    /**
     * @param array $fieldsData
     * @return array
     * @throws NotFoundException
     */
    public function getData(array $fieldsData);
}
