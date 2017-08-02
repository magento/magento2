<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Reader;

use Magento\Framework\View\Layout;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;

/**
 * Backend block structure reader with ACL support
 * @api
 * @since 2.0.0
 */
class Block extends Layout\Reader\Block
{
    /**
     * Initialize dependencies.
     *
     * @param Layout\ScheduledStructure\Helper $helper
     * @param Layout\Argument\Parser $argumentParser
     * @param Layout\ReaderPool $readerPool
     * @param InterpreterInterface $argumentInterpreter
     * @param Condition $conditionReader
     * @param string|null $scopeType
     * @since 2.0.0
     */
    public function __construct(
        Layout\ScheduledStructure\Helper $helper,
        Layout\Argument\Parser $argumentParser,
        Layout\ReaderPool $readerPool,
        InterpreterInterface $argumentInterpreter,
        Condition $conditionReader,
        $scopeType = null
    ) {
        $this->attributes[] = 'acl';
        parent::__construct(
            $helper,
            $argumentParser,
            $readerPool,
            $argumentInterpreter,
            $conditionReader,
            $scopeType
        );
    }
}
