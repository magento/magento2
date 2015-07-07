<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Framework\DB\Select;

interface HandlerInterface
{
    /**
     * @param Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(Select $select, SourceProviderInterface $source, $fieldInfo);

    /**
     * @param Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(Select $select, SourceProviderInterface $source, $fieldInfo);
}
