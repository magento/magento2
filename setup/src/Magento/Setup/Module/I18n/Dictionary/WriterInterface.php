<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary;

/**
 * Writer interface
 * @since 2.0.0
 */
interface WriterInterface
{
    /**
     * Write data to dictionary
     *
     * @param Phrase $phrase
     * @return void
     * @since 2.0.0
     */
    public function write(Phrase $phrase);
}
