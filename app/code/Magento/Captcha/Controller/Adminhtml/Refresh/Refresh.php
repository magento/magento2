<?php
/**
 * Refreshes captcha and returns JSON encoded URL to image (AJAX action)
 * Example: {'imgSrc': 'http://example.com/media/captcha/67842gh187612ngf8s.png'}
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Controller\Adminhtml\Refresh;

/**
 * Class \Magento\Captcha\Controller\Adminhtml\Refresh\Refresh
 *
 * @since 2.0.0
 */
class Refresh extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    protected $serializer;

    /**
     * @var \Magento\Captcha\Helper\Data
     * @since 2.2.0
     */
    protected $captchaHelper;

    /**
     * Refresh constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Captcha\Helper\Data $captchaHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->captchaHelper = $captchaHelper;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function execute()
    {
        $formId = $this->getRequest()->getPost('formId');
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $this->_view->getLayout()->createBlock(
            $captchaModel->getBlockName()
        )->setFormId(
            $formId
        )->setIsAjax(
            true
        )->toHtml();
        $this->getResponse()->representJson($this->serializer->serialize(['imgSrc' => $captchaModel->getImgSrc()]));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        return true;
    }
}
