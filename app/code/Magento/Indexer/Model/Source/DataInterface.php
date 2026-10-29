<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Source;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface \Magento\Indexer\Model\Source\DataInterface
 *
 * @api
 */
interface DataInterface
{
    /**
     * @param array $fieldsData
     * @return array
     * @throws NotFoundException
     */
    public function getData(array $fieldsData);
}
