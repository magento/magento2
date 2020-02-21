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
use Magento\Framework\Image\Adapter\AdapterInterface;
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
     * @param Action\Context $context
     * @param DecoderInterface $urlDecoder
     * @param RawFactory $resultRawFactory
     * @param File $file
     */
    public function __construct(
        Action\Context $context,
        DecoderInterface $urlDecoder,
        RawFactory $resultRawFactory,
        File $file
    ) {
        parent::__construct($context);
        $this->urlDecoder = $urlDecoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->file = $file;
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
            /** @var AdapterInterface $image */
            $image = $this->_objectManager->get(\Magento\Framework\Image\AdapterFactory::class)->create();
            /** @var Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $image->open($imagePath);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            unset($image);
            $resultRaw->setContents($this->file->fileGetContents($imagePath));
        } catch (\Exception $e) {
            /** @var Config $config */
            $config = $this->_objectManager->get(Config::class);
            $imagePath = $config->getSkinImagePlaceholderPath();
            $image->open($imagePath);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        return $resultRaw;
    }
}
