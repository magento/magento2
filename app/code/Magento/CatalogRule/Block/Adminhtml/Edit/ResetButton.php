<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Render reset button
 *
 * Class \Magento\CatalogRule\Block\Adminhtml\Edit\ResetButton
 */
class ResetButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get data for reset button
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('reset')) {
            $data = [
                'id' => 'reset_button',
                'label' => __('Reset'),
                'class' => 'reset',
                'on_click' => 'location.reload();',
                'sort_order' => 30,
            ];
        }
        return $data;
    }
}
