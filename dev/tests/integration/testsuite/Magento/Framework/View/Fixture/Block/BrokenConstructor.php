<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Fixture\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class BrokenConstructor extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @throws LocalizedException
     */
    public function __construct()
    {
        throw new LocalizedException(new Phrase('Construction problem.'));
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '<p>Rendered with construction problem.</p>';
    }
}
