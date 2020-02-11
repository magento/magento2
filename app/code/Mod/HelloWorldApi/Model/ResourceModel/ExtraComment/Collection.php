<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model\ResourceModel\ExtraComment;

use Mod\HelloWorldApi\Model\ExtraComment;
use Mod\HelloWorldApi\Model\ResourceModel\ExtraComment as ExtraCommentResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Comment Collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ExtraComment::class, ExtraCommentResource::class);
    }
}
