<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class DefaultHandler implements HandlerInterface
{
    /**
     * @param \Zend_Db_Select $select
     * @param SourceInterface $source
     * @param array $fieldName
     * @return void
     */
    public function prepareSql(\Zend_Db_Select $select, SourceInterface $source, $fieldName)
    {
        $select->columns(new \Zend_Db_Expr($source->getRealField($fieldName)), $fieldName);
    }

    /**
     * @param \Zend_Db_Select $select
     * @param SourceInterface $source
     * @param array $fieldName
     * @return void
     */
    public function prepareData(\Zend_Db_Select $select, SourceInterface $source, $fieldName)
    {
        // TODO: Implement prepareData() method.
    }
}
