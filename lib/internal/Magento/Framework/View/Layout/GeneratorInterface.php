<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

interface GeneratorInterface
{
    /**
     * Traverse through all elements of specified schedule structural elements of it
     *
     * @param Reader\Context $readerContext
     * @param Generator\Context $generatorContext
     * @return $this
     */
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext);

    /**
     * Return type of generator
     *
     * @return string
     */
    public function getType();
}
