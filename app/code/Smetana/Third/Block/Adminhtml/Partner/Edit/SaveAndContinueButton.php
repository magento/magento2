<?php
namespace Smetana\Third\Block\Adminhtml\Partner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Save and continue button class
 *
 * @package Smetana\Third\Block\Adminhtml\Partner\Edit
 */
class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit']
                ],
            ],
            'sort_order' => 90,
        ];
    }
}
