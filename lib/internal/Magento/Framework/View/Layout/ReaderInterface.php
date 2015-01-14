<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

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
