<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Block;

use Magento\Backend\Test\Block\Messages as AbstractMessages;

/**
 * Search result messages block.
 */
class Messages extends AbstractMessages
{
    /**
     * Notice message selector.
     *
     * @var string
     */
    protected $noticeMessage = '.message.notice';
}
