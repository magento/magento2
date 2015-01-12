<?php
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Controller\Adminhtml\Refresh;

class Refresh extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $formId = $this->getRequest()->getPost('formId');
        $captchaModel = $this->_objectManager->get('Magento\Captcha\Helper\Data')->getCaptcha($formId);
        $this->_view->getLayout()->createBlock(
            $captchaModel->getBlockName()
        )->setFormId(
            $formId
        )->setIsAjax(
            true
        )->toHtml();
        $this->getResponse()->representJson(json_encode(['imgSrc' => $captchaModel->getImgSrc()]));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
