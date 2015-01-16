<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg;

use Magento\Backend\App\Action;

class Directive extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct($context);
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Template directives callback
     *
     * @todo: move this to some model
     *
     * @return void
     */
    public function execute()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = $this->urlDecoder->decode($directive);
        $imagePath = $this->_objectManager->create('Magento\Cms\Model\Template\Filter')->filter($directive);
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $response = $this->getResponse();
        try {
            $image->open($imagePath);
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
        } catch (\Exception $e) {
            $imagePath = $this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')->getSkinImagePlaceholderPath();
            $image->open($imagePath);
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
    }
}
