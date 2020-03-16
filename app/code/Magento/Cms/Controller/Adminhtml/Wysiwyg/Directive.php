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
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Url\DecoderInterface;
use Psr\Log\LoggerInterface;

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
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * @var AdapterFactory
     */
    private $imageAdapterFactory;

    /**
     * @param Action\Context $context
     * @param DecoderInterface $urlDecoder
     * @param RawFactory $resultRawFactory
     * @param File $file
     * @param AdapterFactory $imageAdapterFactory
     */
    public function __construct(
        Action\Context $context,
        DecoderInterface $urlDecoder,
        RawFactory $resultRawFactory,
        File $file,
        AdapterFactory $imageAdapterFactory
    ) {
        parent::__construct($context);
        $this->urlDecoder = $urlDecoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->file = $file;
        $this->imageAdapterFactory = $imageAdapterFactory;
    }

    /**
     * Template directives callback
     *
     * @return Raw
     */
    public function execute()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = $this->urlDecoder->decode($directive);
        try {
            /** @var Filter $filter */
            $filter = $this->_objectManager->create(Filter::class);
            $imagePath = $filter->filter($directive);
            $image = $this->imageAdapterFactory->create();
            $image->open($imagePath);
        } catch (\Exception $e) {
            /** @var Config $config */
            $config = $this->_objectManager->get(Config::class);
            $image = $this->imageAdapterFactory->create();
            $imagePath = $config->getSkinImagePlaceholderPath();
            $image->open($imagePath);
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $mimeType = $image->getMimeType();
        unset($image);
        // To avoid issues with PNG images with alpha blending we return raw file
        // after validation as an image source instead of generating the new PNG image
        // with image adapter
        $content = $this->file->fileGetContents($imagePath);
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader('Content-Type', $mimeType);
        $resultRaw->setContents($content);
        return $resultRaw;
    }
}
