<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Fieldset renderer for PayPal solutions group
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

/**
 * Class \Magento\Paypal\Block\Adminhtml\System\Config\Fieldset\Group
 *
 * @since 2.0.0
 */
class Group extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Return header comment part of html for fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $element->getGroup();

        if (empty($groupConfig['help_url']) || !$element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' .
            $element->getComment() .
            ' <a target="_blank" href="' .
            $groupConfig['help_url'] .
            '">' .
            __(
                'Help'
            ) . '</a></div>';

        return $html;
    }

    /**
     * Return collapse state
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     * @since 2.0.0
     */
    protected function _isCollapseState($element)
    {
        $extra = $this->_authSession->getUser()->getExtra();
        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        $groupConfig = $element->getGroup();
        if (!empty($groupConfig['expanded'])) {
            return true;
        }

        return false;
    }
}
