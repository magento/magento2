<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Layout;

/**
 * Interface \Magento\Framework\View\Layout\ReaderInterface
 *
 * @api
 */
interface ReaderInterface
{
    /**
     * Read children elements structure and fill scheduled structure
     *
     * @param Reader\Context $readerContext
     * @param Element $element
     * @return $this
     */
    public function interpret(Reader\Context $readerContext, Element $element);

    /**
     * Get nodes types that current reader is support
     *
     * @return string[]
     */
    public function getSupportedNodes();
}
