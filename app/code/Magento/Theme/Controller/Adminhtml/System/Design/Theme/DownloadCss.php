<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Psr\Log\LoggerInterface;

/**
 * Class for Download Css.
 * @deprecated 100.2.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class DownloadCss extends Theme implements HttpGetActionInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * DownloadCss constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param Repository $assetRepo
     * @param Filesystem $appFileSystem
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        Repository $assetRepo,
        Filesystem $appFileSystem,
        Escaper $escaper = null
    ) {
        $this->escaper = $escaper ?? $context->getObjectManager()->get(Escaper::class);
        parent::__construct($context, $coreRegistry, $fileFactory, $assetRepo, $appFileSystem);
    }

    /**
     * Download css file
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('theme_id');
        $file = $this->getRequest()->getParam('file');

        /** @var $urlDecoder DecoderInterface */
        $urlDecoder = $this->_objectManager->get(DecoderInterface::class);
        $fileId = $urlDecoder->decode($file);
        try {
            /** @var $theme ThemeInterface */
            $theme = $this->_objectManager->create(ThemeInterface::class)->load($themeId);
            if (!$theme->getId()) {
                throw new \InvalidArgumentException(sprintf('Theme not found: "%d".', $themeId));
            }
            $asset = $this->_assetRepo->createAsset($fileId, ['themeModel' => $theme]);
            $relPath = $this->_appFileSystem->getDirectoryRead(DirectoryList::ROOT)
                ->getRelativePath($asset->getSourceFile());

            return $this->_fileFactory->create(
                $relPath,
                [
                    'type'  => 'filename',
                    'value' => $relPath
                ],
                DirectoryList::ROOT
            );
        } catch (\InvalidArgumentException $e) {
            $this->messageManager->addException($e, __('Theme not found: "%1".', $this->escaper->escapeHtml($themeId)));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('File not found: "%1".', $this->escaper->escapeHtml($fileId)));
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
    }
}
