<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Source;

use Magento\Framework\Exception\NotFoundException;

/**
 * Interface \Magento\Indexer\Model\Source\DataInterface
 *
 * @since 2.0.0
 */
interface DataInterface
{
    /**
     * @param array $fieldsData
     * @return array
     * @throws NotFoundException
     * @since 2.0.0
     */
    public function getData(array $fieldsData);
}
