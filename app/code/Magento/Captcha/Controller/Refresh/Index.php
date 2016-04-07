<?php
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Controller\Refresh;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $captchaHelper;

    /**
     * @param Context $context
     * @param \Magento\Captcha\Helper\Data $captchaHelper
     */
    public function __construct(Context $context, \Magento\Captcha\Helper\Data $captchaHelper)
    {
        $this->captchaHelper = $captchaHelper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $formId = $this->_request->getPost('formId');
        if (null === $formId) {
            try {
                $params = [];
                $content = $this->_request->getContent();
                if ($content) {
                    $params = \Zend_Json::decode($content);
                }
                $formId = isset($params['formId']) ? $params['formId'] : null;
            } catch (\Zend_Json_Exception $exception) {
                $formId = null;
            }
        }
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $captchaModel->generate();

        $block = $this->_view->getLayout()->createBlock($captchaModel->getBlockName());
        $block->setFormId($formId)->setIsAjax(true)->toHtml();
        $this->_response->representJson(json_encode(['imgSrc' => $captchaModel->getImgSrc()]));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
