<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Controller\Refresh;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\LayoutInterface;

/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 */
class Index implements HttpPostActionInterface
{
    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @param RequestInterface $request
     * @param JsonResultFactory $jsonFactory
     * @param CaptchaHelper $captchaHelper
     * @param LayoutInterface $layout
     * @param JsonSerializer $serializer
     */
    public function __construct(
        RequestInterface $request,
        JsonResultFactory $jsonFactory,
        CaptchaHelper $captchaHelper,
        LayoutInterface $layout,
        JsonSerializer $serializer
    ) {
        $this->request = $request;
        $this->jsonResultFactory = $jsonFactory;
        $this->captchaHelper = $captchaHelper;
        $this->layout = $layout;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $formId = $this->request->getPost('formId');
        if (null === $formId) {
            $params = [];
            $content = $this->request->getContent();
            if ($content) {
                $params = $this->serializer->unserialize($content);
            }
            $formId = isset($params['formId']) ? $params['formId'] : null;
        }
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $captchaModel->generate();

        $block = $this->layout->createBlock($captchaModel->getBlockName());
        $block->setFormId($formId)->setIsAjax(true)->toHtml();

        return $this->jsonResultFactory->create(['imgSrc' => $captchaModel->getImgSrc()]);
    }
}
