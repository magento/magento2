<?php
/**
 * Adminhtml AdminNotification Severity Renderer
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
namespace Magento\AdminNotification\Block\Grid\Renderer;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Core\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Core\Helper\Url $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Core\Helper\Url $urlHelper,
        array $data = array()
    ) {
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\Object $row
     * @return  string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $readDetailsHtml = $row->getUrl() ? '<a class="action-details" target="_blank" href="' . $row->getUrl() . '">' . __(
            'Read Details'
        ) . '</a>' : '';

        $markAsReadHtml = !$row->getIsRead() ? '<a class="action-mark" href="' . $this->getUrl(
            '*/*/markAsRead/',
            array('_current' => true, 'id' => $row->getId())
        ) . '">' . __(
            'Mark as Read'
        ) . '</a>' : '';

        $encodedUrl = $this->_urlHelper->getEncodedUrl();
        return sprintf(
            '%s%s<a class="action-delete" href="%s" onClick="deleteConfirm(\'%s\', this.href); return false;">%s</a>',
            $readDetailsHtml,
            $markAsReadHtml,
            $this->getUrl(
                '*/*/remove/',
                array(
                    '_current' => true,
                    'id' => $row->getId(),
                    \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $encodedUrl
                )
            ),
            __('Are you sure?'),
            __('Remove')
        );
    }
}
