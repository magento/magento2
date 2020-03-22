<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Controller\Refresh;

use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 */
class Index extends Action implements HttpPostActionInterface
{
    /**
     * @var Data
     */
    private $captchaHelper;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param Data $captchaHelper
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Data $captchaHelper,
        Json $serializer
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
            $formId = $params['formId'] ?? null;
        }
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $captchaModel->generate();

        $block = $this->_view->getLayout()->createBlock($captchaModel->getBlockName());
        $block->setFormId($formId)->setIsAjax(true)->toHtml();
        $this->_response->representJson($this->serializer->serialize(['imgSrc' => $captchaModel->getImgSrc()]));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
