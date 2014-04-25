<?php
/**
 * Renders "Activate" link.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;

use Magento\Integration\Model\Integration;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Link;
use Magento\Framework\Object;

class Activate extends Link
{
    /**
     * {@inheritDoc}
     */
    public function getCaption()
    {
        return $this->_row->getStatus() == Integration::STATUS_INACTIVE ? __('Activate') : __('Reauthorize');
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getUrl(Object $row)
    {
        return 'javascript:void(0);';
    }

    /**
     * {@inheritDoc}
     */
    protected function _getAttributes()
    {
        return array_merge(parent::_getAttributes(), array('onclick' => 'integration.popup.show(this);'));
    }

    /**
     * {@inheritDoc}
     */
    protected function _getDataAttributes()
    {
        return array(
            'row-id' => $this->_row->getId(),
            'row-dialog' => 'permissions',
            'row-is-reauthorize' => $this->_row->getStatus() == Integration::STATUS_INACTIVE ? '0' : '1',
            'row-is-token-exchange' => $this->_row->getEndpoint() && $this->_row->getIdentityLinkUrl() ? '1' : '0'
        );
    }
}
