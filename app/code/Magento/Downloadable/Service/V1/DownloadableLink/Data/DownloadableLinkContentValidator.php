<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Downloadable\Service\V1\Data\FileContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Url\Validator as UrlValidator;

class DownloadableLinkContentValidator
{
    /**
     * @var FileContentValidator
     */
    protected $fileContentValidator;

    /**
     * @var UrlValidator
     */
    protected $urlValidator;

    /**
     * @param FileContentValidator $fileContentValidator
     * @param UrlValidator $urlValidator
     */
    public function __construct(
        FileContentValidator $fileContentValidator,
        UrlValidator $urlValidator
    ) {
        $this->fileContentValidator = $fileContentValidator;
        $this->urlValidator = $urlValidator;
    }

    /**
     * Check if link content is valid
     *
     * @param DownloadableLinkContent $linkContent
     * @return bool
     * @throws InputException
     */
    public function isValid(DownloadableLinkContent $linkContent)
    {
        if (!is_numeric($linkContent->getPrice()) || $linkContent->getPrice() < 0) {
            throw new InputException('Link price must have numeric positive value.');
        }
        if (!is_int($linkContent->getNumberOfDownloads()) || $linkContent->getNumberOfDownloads() < 0) {
            throw new InputException('Number of downloads must be a positive integer.');
        }
        if (!is_int($linkContent->getSortOrder()) || $linkContent->getSortOrder() < 0) {
            throw new InputException('Sort order must be a positive integer.');
        }

        $this->validateLinkResource($linkContent);
        $this->validateSampleResource($linkContent);
        return true;
    }

    /**
     * Validate link resource (file or URL)
     *
     * @param DownloadableLinkContent $linkContent
     * @throws InputException
     * @return void
     */
    protected function validateLinkResource(DownloadableLinkContent $linkContent)
    {
        if ($linkContent->getLinkType() == 'url'
            && !$this->urlValidator->isValid($linkContent->getLinkUrl())
        ) {
            throw new InputException('Link URL must have valid format.');
        }
        if ($linkContent->getLinkType() == 'file'
            && (!$linkContent->getLinkFile() || !$this->fileContentValidator->isValid($linkContent->getLinkFile()))
        ) {
            throw new InputException('Provided file content must be valid base64 encoded data.');
        }
    }

    /**
     * Validate sample resource (file or URL)
     *
     * @param DownloadableLinkContent $linkContent
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(DownloadableLinkContent $linkContent)
    {
        if ($linkContent->getSampleType() == 'url'
            && !$this->urlValidator->isValid($linkContent->getSampleUrl())
        ) {
            throw new InputException('Sample URL must have valid format.');
        }
        if ($linkContent->getSampleType() == 'file'
            && (!$linkContent->getSampleFile() || !$this->fileContentValidator->isValid($linkContent->getSampleFile()))
        ) {
            throw new InputException('Provided file content must be valid base64 encoded data.');
        }
    }
}
