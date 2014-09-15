<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model\Theme\Image;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme Image Path
 */
class Path implements \Magento\Framework\View\Design\Theme\Image\PathInterface
{
    /**
     * Default theme preview image
     */
    const DEFAULT_PREVIEW_IMAGE = 'Magento_Core::theme/default_preview.jpg';

    /**
     * Media Directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Initialize dependencies
     * 
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $this->assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
    }

    /**
     * Get url to preview image
     *
     * @param \Magento\Core\Model\Theme|ThemeInterface $theme
     * @return string
     */
    public function getPreviewImageUrl(ThemeInterface $theme)
    {
        return $theme->isPhysical()
            ? $this->assetRepo->getUrlWithParams(
                $theme->getPreviewImage(),
                ['area' => $theme->getData('area'), 'themeModel' => $theme]
            )
            : $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage();
    }

    /**
     * Get path to preview image
     *
     * @param \Magento\Core\Model\Theme|ThemeInterface $theme
     * @return string
     */
    public function getPreviewImagePath(ThemeInterface $theme)
    {
        return $theme->isPhysical()
            ? $this->assetRepo->createAsset(
                $theme->getPreviewImage(),
                ['area' => $theme->getData('area'), 'themeModel' => $theme]
            )
            ->getSourceFile()
            : $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage());
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    public function getPreviewImageDefaultUrl()
    {
        return $this->assetRepo->getUrl(self::DEFAULT_PREVIEW_IMAGE);
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    public function getImagePreviewDirectory()
    {
        return $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH);
    }

    /**
     * Temporary directory path to store images
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->mediaDirectory->getRelativePath('/theme/origin');
    }
}
