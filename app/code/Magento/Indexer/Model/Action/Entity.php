<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\DB\Select;
use Magento\Indexer\Model\HandlerInterface;

class Entity extends Base
{
    /**
     * @var string
     */
    protected $tableAlias = 'e';
}
