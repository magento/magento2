<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Interface \Magento\Framework\View\Layout\ReaderInterface
 *
 * @since 2.0.0
 */
interface ReaderInterface
{
    /**
     * Read children elements structure and fill scheduled structure
     *
     * @param Reader\Context $readerContext
     * @param Element $element
     * @return $this
     * @since 2.0.0
     */
    public function interpret(Reader\Context $readerContext, Element $element);

    /**
     * Get nodes types that current reader is support
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getSupportedNodes();
}
