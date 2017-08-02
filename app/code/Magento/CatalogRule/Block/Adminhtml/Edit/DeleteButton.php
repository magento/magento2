<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @since 2.1.0
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId && $this->canRender('delete')) {
            $data = [
                'label' => __('Delete Rule'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->urlBuilder->getUrl('*/*/delete', ['id' => $ruleId]) . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
