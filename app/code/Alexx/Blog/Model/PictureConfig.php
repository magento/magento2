<?php

namespace Alexx\Blog\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Urls for blog pictures
 */
class PictureConfig
{
    protected $storeManager;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve base url
     * */
    public function getBeforeMediaUrl()
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Retrieve base url for media files
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->getBeforeMediaUrl() . 'blog';
    }

    /**
     * Retrieve url for media file
     *
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBeforeMediaUrl() . $this->_prepareFile($file);
    }

    /**
     * Retrieve url for image to display
     *
     * @param string $file
     * @return string
     */
    public function getBlogImageUrl($file)
    {
        return (
        ($file == '' || $file === null) ?
            $this->getViewFileUrl('Alexx_Blog::images/image-placeholder.png') :
            $this->getMediaUrl($file));
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        $params = array_merge(
            [
                '_secure' => ObjectManager::getInstance()
                    ->get(\Magento\Framework\App\Action\Context::class)
                    ->getRequest()->isSecure()
            ],
            $params
        );
        return ObjectManager::getInstance()->get(\Magento\Framework\View\Asset\Repository::class)
            ->getUrlWithParams($fileId, $params);
    }

    /**
     * Get filesystem directory path for product images relative to the media directory.
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return 'blog';
    }

    /**
     * Process file path.
     *
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
