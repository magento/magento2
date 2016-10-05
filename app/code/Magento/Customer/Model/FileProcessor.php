<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;

class FileProcessor
{
    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * @param Filesystem $filesystem
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     * @param string $entityTypeCode
     */
    public function __construct(
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        EncoderInterface $urlEncoder,
        $entityTypeCode
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->urlBuilder = $urlBuilder;
        $this->urlEncoder = $urlEncoder;
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * Check if the file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName)
    {
        $filePath = $this->entityTypeCode . '/' . ltrim($fileName, '/');

        $result = $this->mediaDirectory->isExist($filePath);
        return $result;
    }

    /**
     * Retrieve customer/index/viewfile action URL
     *
     * @param string $filePath
     * @param string $type
     * @return string
     */
    public function getViewUrl($filePath, $type)
    {
        $viewUrl = '';

        if ($this->entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $filePath = $this->entityTypeCode . '/' . ltrim($filePath, '/');
            $viewUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
                . $this->mediaDirectory->getRelativePath($filePath);
        }

        if ($this->entityTypeCode == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $viewUrl = $this->urlBuilder->getUrl(
                'customer/index/viewfile',
                [$type => $this->urlEncoder->encode(ltrim($filePath, '/'))]
            );
        }

        return $viewUrl;
    }
}
