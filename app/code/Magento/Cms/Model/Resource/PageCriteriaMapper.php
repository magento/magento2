<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Resource;

/**
 * Class PageCriteriaMapper
 */
class PageCriteriaMapper extends CmsCriteriaMapper
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->storeTableName = 'cms_page_store';
        $this->linkFieldName = 'page_id';
        $this->initResource('Magento\Cms\Model\Resource\Page');
        parent::init();
    }
}
