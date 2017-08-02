<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\SaveButton
 *
 * @since 2.1.0
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     * @since 2.1.0
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->canRender('save')) {
            $data = [
                'label' => __('Save'),
                'class' => 'save primary',
                'on_click' => '',
            ];
        }
        return $data;
    }
}
