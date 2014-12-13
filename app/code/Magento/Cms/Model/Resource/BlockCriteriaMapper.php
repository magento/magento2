<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
