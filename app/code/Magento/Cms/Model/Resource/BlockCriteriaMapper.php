<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Resource;

/**
 * Class BlockCriteriaMapper
 */
class BlockCriteriaMapper extends CmsCriteriaMapper
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->storeTableName = 'cms_block_store';
        $this->linkFieldName = 'block_id';
        $this->initResource('Magento\Cms\Model\Resource\Block');
        parent::init();
    }
}
