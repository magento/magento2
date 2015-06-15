<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Handler;

use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Indexer\Model\HandlerInterface;

class DefaultHandler implements HandlerInterface
{
    /**
     * @param \Zend_Db_Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(\Zend_Db_Select $select, SourceProviderInterface $source, $fieldInfo)
    {
        $select->columns([$fieldInfo['name'] => $fieldInfo['origin']], $source->getEntityName());
    }

    /**
     * @param \Zend_Db_Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(\Zend_Db_Select $select, SourceProviderInterface $source, $fieldInfo)
    {
        new \Exception('Not implemented yet');
    }
}
