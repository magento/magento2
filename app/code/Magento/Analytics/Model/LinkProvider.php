<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Analytics\Api\LinkProviderInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;

/**
 * Class LinkRepository
 * {@inheritdoc}
 */
class LinkProvider implements LinkProviderInterface
{
    /**
     * @var LinkInterfaceFactory
     */
    private $linkInterfaceFactory;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @param MediaConfig $mediaConfig
     * @param LinkInterfaceFactory $linkInterfaceFactory
     */
    public function __construct(
        MediaConfig $mediaConfig,
        LinkInterfaceFactory $linkInterfaceFactory
    ) {
        $this->mediaConfig = $mediaConfig;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $flagData = ['path' => 'Documents/data.tgz', 'iv' => '42'];
        return $this->linkInterfaceFactory->create(
            [
                'data' => [
                    'url' => $this->mediaConfig->getMediaUrl($flagData['path']),
                    'iv' => $flagData['iv']
                ]
            ]
        );
    }
}
