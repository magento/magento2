<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Block\Adminhtml;

/**
 * Adminhtml Google Content Captcha challenge
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Captcha extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'captcha.phtml';

    /**
     * Get HTML code for confirm captcha button
     *
     * @return string
     */
    public function getConfirmButtonHtml()
    {
        $confirmButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'label' => __('Confirm'),
                'onclick' => "if($('user_confirm').value != '')
                                {
                                    setLocation('" .
                $this->getUrl(
                    'adminhtml/*/confirmCaptcha',
                    ['_current' => true]
                ) . "' + 'user_confirm/' + $('user_confirm').value + '/');
                                }",
                'class' => 'task',
            ]
        );
        return $confirmButton->toHtml();
    }
}
