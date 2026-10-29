<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
