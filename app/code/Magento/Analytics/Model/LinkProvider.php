<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Analytics\Api\LinkProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides link to file with collected report data.
 */
class LinkProvider implements LinkProviderInterface
{
    /**
     * @var LinkInterfaceFactory
     */
    private $linkInterfaceFactory;

    /**
     * @var FileInfoManager
     */
    private $fileInfoManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param FileInfoManager $fileInfoManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LinkInterfaceFactory $linkInterfaceFactory,
        FileInfoManager $fileInfoManager,
        StoreManagerInterface $storeManager
    ) {
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->fileInfoManager = $fileInfoManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $fileInfo = $this->fileInfoManager->load();
        if ($fileInfo->getPath() === null || $fileInfo->getInitializationVector() === null) {
            throw new Exception(__('File is not ready yet.'), 0, Exception::HTTP_NOT_FOUND);
        }
        $link = $this->linkInterfaceFactory->create();
        $link->setUrl(
            $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $fileInfo->getPath()
        );
        $link->setInitializationVector(base64_encode($fileInfo->getInitializationVector()));
        return $link;
    }
}
