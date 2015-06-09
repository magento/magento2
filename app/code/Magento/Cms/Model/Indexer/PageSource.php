<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Indexer;

use Magento\Cms\Model\Resource\Page;
use Magento\Indexer\Model\SourceInterface;

class PageSource implements SourceInterface
{
    /**
     * @var \Zend_Db_Select
     */
    protected $select;

    /**
     * @var Page
     */
    protected $resourcePage;

    /**
     * @var []
     */
    protected $fields;

    public function __construct(
        Page $resourcePage
    ) {
        $this->resourcePage = $resourcePage;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->resourcePage->getMainTable();
    }
}
