<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $url = $this->_objectManager->create('Magento\Email\Model\Template\Filter')->filter($directive);
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
        $response = $this->getResponse();
        try {
            $image->open($url);
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
        } catch (\Exception $e) {
            $image->open($this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')->getSkinImagePlaceholderUrl());
            $response->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }
}
