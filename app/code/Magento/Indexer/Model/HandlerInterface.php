<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\App\Resource\SourceProviderInterface;

interface HandlerInterface
{
    /**
     * @param \Zend_Db_Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareSql(\Zend_Db_Select $select, SourceProviderInterface $source, $fieldInfo);

    /**
     * @param \Zend_Db_Select $select
     * @param SourceProviderInterface $source
     * @param array $fieldInfo
     * @return void
     */
    public function prepareData(\Zend_Db_Select $select, SourceProviderInterface $source, $fieldInfo);
}
