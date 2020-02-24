<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Thumbnail class returns raw image thumbnails for WYSIWYG Media Browser
 */
class Thumbnail extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param Repository $assetRepo
     * @param LoggerInterface|null $logger
     * @param AdapterFactory $adapterFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        Repository $assetRepo,
        LoggerInterface $logger,
        AdapterFactory $adapterFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->assetRepo = $assetRepo;
        $this->logger = $logger;
        $this->adapterFactory = $adapterFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Generate image thumbnail on the fly
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $file = $this->_objectManager->get(\Magento\Cms\Helper\Wysiwyg\Images::class)->idDecode($file);
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
        $image = $this->adapterFactory->create();
        try {
            $thumb = $this->getStorage()->resizeOnTheFly($file);
            $image->open($thumb);
            $resultRaw->setHeader('Content-Type', $image->getMimeType());
            $resultRaw->setContents($image->getImage());
            return $resultRaw;
        } catch (\Exception $e) {
            //The thumbnail could not be generated using the current Image Adapter. Use CMS Thumb placeholder instead.
            try {
                $thumb = $this->assetRepo->createAsset(Storage::THUMB_PLACEHOLDER_PATH_SUFFIX)->getSourceFile();
                $image->open($thumb);
                $resultRaw->setHeader('Content-Type', $image->getMimeType());
                $resultRaw->setContents($image->getImage());
                $this->logger->warning($e);
            } catch (\Exception $e) {
                $this->logger->warning($e);
            }
        }
        return $resultRaw;
    }
}
