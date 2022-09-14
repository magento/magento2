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
     * Key `view_model`
     */
    private const VIEW_MODEL = 'view_model';

    /**
     * @var ArgumentInterface
     */
    private $viewModel;

    /**
     * @param ArgumentInterface $viewModel
     */
    public function __construct(ArgumentInterface $viewModel)
    {
        $this->viewModel = $viewModel;
    }

    /**
     * Sets the view model before rendering to HTML
     *
     * @param DataObject|BlockInterface $block
     * @return null
     */
    public function beforeToHtml(DataObject $block)
    {
        if (!$block->hasData(self::VIEW_MODEL)) {
            $block->setData(self::VIEW_MODEL, $this->viewModel);
        }

        return null;
    }
}
