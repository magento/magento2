<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Cache\Grid\Massaction;

use Magento\Backend\Block\Widget\Grid\Massaction\VisibilityCheckerInterface;
use Magento\Framework\App\State;

/**
 * Class checks that action can be displayed on massaction list
 * @since 2.2.0
 */
class ProductionModeVisibilityChecker implements VisibilityCheckerInterface
{
    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * @param State $state
     * @since 2.2.0
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function isVisible()
    {
        return $this->state->getMode() !== State::MODE_PRODUCTION;
    }
}
