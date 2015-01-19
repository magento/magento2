<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Dictionary;

/**
 * Writer interface
 */
interface WriterInterface
{
    /**
     * Write data to dictionary
     *
     * @param Phrase $phrase
     * @return void
     */
    public function write(Phrase $phrase);
}
