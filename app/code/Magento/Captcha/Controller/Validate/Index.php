<?php
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Controller\Validate;

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
        try {
            $params = \Zend_Json::decode($this->_request->getContent());
        } catch (\Zend_Json_Exception $exception) {
            $params = [];
        }
        $formId = isset($params['formId']) ? $params['formId'] : null;
        $captchaText = isset($params['captchaText']) ? $params['captchaText'] : null;

        $result = ['error' => 0, 'message' => __('Correct CAPTCHA')];
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($captchaText)) {
                $result = ['error' => 1, 'message' => __('Incorrect CAPTCHA')];
            }
        }
        $this->_response->representJson(\Zend_Json::encode($result));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
