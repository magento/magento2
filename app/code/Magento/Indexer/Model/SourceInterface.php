<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

interface SourceInterface
{
    /**
     * @param array $fieldName
     * @return string
     */
    public function getRealField($fieldName);

    /**
     * @return string
     */
    public function getTableName();
}
