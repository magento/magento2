<?php
namespace Smetana\Third\Block\Adminhtml\Partner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete button class
 *
 * @package Smetana\Third\Block\Adminhtml\Partner\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to delete Partner?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * Get delete url path
     *
     * @return string
     */
    private function getDeleteUrl(): string
    {
        return $this->getPath('*/*/delete', ['id' => $this->getId()]);
    }
}
