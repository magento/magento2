<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Analytics\Api\LinkProviderInterface;
use Magento\Framework\UrlInterface;
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
        $link = $this->linkInterfaceFactory->create();
        $link->setUrl(
            $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $fileInfo->getPath()
        );
        $link->setInitializationVector($fileInfo->getInitializationVector());
        return $link;
    }
}
