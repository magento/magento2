<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Template\Filter;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Process template text for wysiwyg editor.
 */
class Directive extends Action implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::media_gallery';

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->urlDecoder = $urlDecoder;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Template directives callback
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = $this->urlDecoder->decode($directive);
        try {
            /** @var Filter $filter */
            $filter = $this->_objectManager->create(Filter::class);
            $imagePath = $filter->filter($directive);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            $image = $this->_objectManager->get(\Magento\Framework\Image\AdapterFactory::class)->create();
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $image->open($imagePath);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
        } catch (\Exception $e) {
            /** @var Config $config */
            $config = $this->_objectManager->get(Config::class);
            $imagePath = $config->getSkinImagePlaceholderPath();
            $image->open($imagePath);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        return $resultRaw;
    }
}
