<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

/**
 * Interface \Magento\Framework\View\Layout\GeneratorInterface
 *
 * @since 2.0.0
 */
interface GeneratorInterface
{
    /**
     * Traverse through all elements of specified schedule structural elements of it
     *
     * @param Reader\Context $readerContext
     * @param Generator\Context $generatorContext
     * @return $this
     * @since 2.0.0
     */
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext);

    /**
     * Return type of generator
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();
}
