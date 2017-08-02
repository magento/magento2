<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Block\Adminhtml\Synonyms\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 * @since 2.1.0
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     * @since 2.1.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
