<?php
namespace Smetana\Images\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Images helper
 */
class Data extends AbstractHelper
{
    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Adapter Factory
     *
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * File Operations Class
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    public $file;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filesystem\Driver\File $file,
        Context $context
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->file = $file;
    }

    /**
     * Resizing Smetana Image
     *
     * @param string $image
     * @param int $width
     * @param int $height
     *
     * @return string or boolean
     */
    public function resize(string $image, int $width = null, int $height = null)
    {
        $mediaDirectory = $this->_filesystem->getDirectoryRead('media');
        $origPath = $mediaDirectory->getAbsolutePath('products_image/' . $image);
        if (!$this->file->isFile($origPath)) {
            return false;
        }

        $resizePath = $mediaDirectory
            ->getAbsolutePath('products_image/resize/' . $width . $height . '_' . explode('/', $image)[1]);
        if (!$this->file->isFile($mediaDirectory->getAbsolutePath() . $resizePath)) {
            $files = $this->file->readDirectory($mediaDirectory->getAbsolutePath('products_image/resize/'));
            if ($files) {
                foreach ($files as $file) {
                    $this->file->deleteFile($file);
                }
            }
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($origPath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->resize($width, $height);
            $imageResize->save($resizePath);
        }

        return $resizePath;
    }
}
