<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Block\Adminhtml\Edit;

use Magento\Ui\Component\Control\ButtonProviderInterface;

/**
 * Class BackButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => 'setLocation(\'' . $this->getBackUrl() . '\')',
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
