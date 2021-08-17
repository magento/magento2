<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Plugin\UserDataProvider;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Sets the view model
 */
class ViewModel
{
    /**
     * @var ArgumentInterface
     */
    private $argument;

    /**
     * @param ArgumentInterface $argument
     */
    public function __construct(ArgumentInterface $argument)
    {
        $this->argument = $argument;
    }

    /**
     * Sets the view model before rendering to HTML
     *
     * @param DataObject|BlockInterface $block
     * @return null
     */
    public function beforeToHtml(DataObject $block)
    {
        $block->setData('view_model', $this->argument);
        return null;
    }
}
