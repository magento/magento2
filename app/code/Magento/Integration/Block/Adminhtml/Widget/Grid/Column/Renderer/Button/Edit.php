<?php
/**
 * Render HTML <button> tag with "edit" action for the integration grid.
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
namespace Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

use Magento\Framework\Object;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;

class Edit extends Button
{
    /**
     * Return 'onclick' action for the button (redirect to the integration edit page).
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getOnclickAttribute(Object $row)
    {
        return sprintf("window.location.href='%s'", $this->getUrl('*/*/edit', array('id' => $row->getId())));
    }

    /**
     * Get title depending on whether element is disabled or not.
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    protected function _getTitleAttribute(Object $row)
    {
        return $this->_isConfigBasedIntegration($row) ? __('View') : __('Edit');
    }

    /**
     * Get the icon on the grid according to the integration type
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function _getClassAttribute(Object $row)
    {
        $class = $this->_isConfigBasedIntegration($row) ? 'info' : 'edit';

        return 'action ' . $class;
    }
}
