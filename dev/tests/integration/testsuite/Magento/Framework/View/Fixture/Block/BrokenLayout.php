<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Fixture\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\LayoutInterface;

class BrokenLayout extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @param LayoutInterface $layout
     * @throws LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLayout(LayoutInterface $layout)
    {
        throw new LocalizedException(new Phrase('Prepare layout problem.'));
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '<p>Rendered with layout problem.</p>';
    }
}
