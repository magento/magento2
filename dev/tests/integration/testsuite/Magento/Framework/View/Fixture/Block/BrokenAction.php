<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Fixture\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\LayoutInterface;

class BrokenAction extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @param LayoutInterface $layout
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLayout(LayoutInterface $layout)
    {
        return $this;
    }

    /**
     * @param string $action
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initAction($action)
    {
        throw new LocalizedException(new Phrase('Init action problem.'));
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        return '<p>Rendered with action problem.</p>';
    }
}
