<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\View\Asset\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\ContextInterface;

/**
 * Constructor modification point for Magento\Catalog\Model\View\Asset\Image.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 * @since 2.2.0
 */
class Context implements ContextInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.2.0
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\ConfigInterface
     * @since 2.2.0
     */
    private $mediaConfig;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.2.0
     */
    private $filesystem;

    /**
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Media\ConfigInterface $mediaConfig,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->create($this->mediaConfig->getBaseMediaPath());
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getPath()
    {
        return $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseMediaPath());
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getBaseUrl()
    {
        return $this->mediaConfig->getBaseMediaUrl();
    }
}
