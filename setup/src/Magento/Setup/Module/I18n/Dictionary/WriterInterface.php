<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

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
