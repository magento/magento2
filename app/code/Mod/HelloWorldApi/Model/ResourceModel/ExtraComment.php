<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model\ResourceModel;

use Mod\HelloWorldApi\Api\Data\ExtraCommentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * DB ExtraComment class.
 */
class ExtraComment extends AbstractDb
{
    /**
     * @var string
     */
    const TABLE_NAME = 'product_extra_comments';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ExtraCommentInterface::COMMENT_ID);
    }
}
