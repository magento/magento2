<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param string $method
     * @param array $args
     * @return void
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        $this->initializeException();
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    public function toHtml()
    {
        $this->initializeException();
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    protected function initializeException()
    {
        throw new LocalizedException(
            new Phrase('Block %1 throws exception and cannot be rendered.', [$this->blockName])
        );
    }
}
