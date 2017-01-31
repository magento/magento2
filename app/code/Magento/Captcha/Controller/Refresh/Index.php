<?php
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @param Context $context
     * @param \Magento\Captcha\Helper\Data $captchaHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        \Magento\Captcha\Helper\Data $captchaHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->captchaHelper = $captchaHelper;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $formId = $this->_request->getPost('formId');
        if (null === $formId) {
            $params = [];
            $content = $this->_request->getContent();
            if ($content) {
                $params = $this->serializer->unserialize($content);
            }
            $formId = isset($params['formId']) ? $params['formId'] : null;
        }
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $captchaModel->generate();

        $block = $this->_view->getLayout()->createBlock($captchaModel->getBlockName());
        $block->setFormId($formId)->setIsAjax(true)->toHtml();
        $this->_response->representJson($this->serializer->serialize(['imgSrc' => $captchaModel->getImgSrc()]));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
