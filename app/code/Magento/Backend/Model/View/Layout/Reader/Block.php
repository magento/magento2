<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Backend block structure reader with ACL support
 */
class Block extends Layout\Reader\Block
{
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        $scopeType = null
    ) {
        $this->attributes[] = 'acl';
        parent::__construct(
            $helper,
            $argumentParser,
            $readerPool,
            $argumentInterpreter,
            $scopeType
        );
    }
}
