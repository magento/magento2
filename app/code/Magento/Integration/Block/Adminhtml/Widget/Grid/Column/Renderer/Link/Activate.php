<?php
/**
 * Renders "Activate" link.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;

use Magento\Framework\DataObject;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;
use Magento\Integration\Model\Integration;

/**
 * Class \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link\Activate
 *
 * @since 2.0.0
 */
class Activate extends Link
{
    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function getCaption()
    {
        return $this->_row->getStatus() != Integration::STATUS_ACTIVE ? __('Activate') : __('Reauthorize');
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function _getUrl(DataObject $row)
    {
        return 'javascript:void(0);';
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    protected function _getAttributes()
    {
        return array_merge(parent::_getAttributes(), ['onclick' => 'integration.popup.show(this);']);
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    protected function _getDataAttributes()
    {
        return [
            'row-id' => $this->_row->getId(),
            'row-dialog' => 'permissions',
            'row-is-reauthorize' => $this->_row->getStatus() == Integration::STATUS_INACTIVE ? '0' : '1',
            'row-is-token-exchange' => $this->_row->getEndpoint() && $this->_row->getIdentityLinkUrl() ? '1' : '0'
        ];
    }
}
