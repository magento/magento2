<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class of a Exception Handler Block
 *
 * Block for default and maintenance mode. During layout loading process corrupted block (that throws exception)
 * will be replaced with a "dummy" block. As result, page will be loaded without broken block.
 *
 * When calls from parent to child block occurred and the error appeared in the child block,
 * all blocks chain would be removed.
 */
class ExceptionHandlerBlock implements BlockInterface
{
    /**
     * @var string
     */
    protected $blockName;

    /**
     * @param string $blockName
     */
    public function __construct($blockName = '')
    {
        $this->blockName = $blockName;
    }

    /**
     * Throws an exception when parent block calls corrupted child block method
     *
     * @param string $method
     * @param array $args
     * @return void
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        throw new LocalizedException(
            new Phrase('Block %1 throws exception and cannot be rendered.', [$this->blockName])
        );
    }

    /**
     * Declared in BlockInterface and also throws an exception
     *
     * @throws LocalizedException
     * @return void
     */
    public function toHtml()
    {
        throw new LocalizedException(
            new Phrase('Block %1 throws exception and cannot be rendered.', [$this->blockName])
        );
    }
}
