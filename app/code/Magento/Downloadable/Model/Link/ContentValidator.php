<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Url\Validator as UrlValidator;

/**
 * Class \Magento\Downloadable\Model\Link\ContentValidator
 *
 */
class ContentValidator
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
     * @param LinkInterface $link
     * @param bool $validateLinkContent
     * @param bool $validateSampleContent
     * @return bool
     * @throws InputException
     */
    public function isValid(LinkInterface $link, $validateLinkContent = true, $validateSampleContent = true)
    {
        if (!is_numeric($link->getPrice()) || $link->getPrice() < 0) {
            throw new InputException(__('Link price must have numeric positive value.'));
        }
        if (filter_var($link->getNumberOfDownloads(), FILTER_VALIDATE_INT) === false
            || $link->getNumberOfDownloads() < 0) {
            throw new InputException(__('Number of downloads must be a positive integer.'));
        }
        if (filter_var($link->getSortOrder(), FILTER_VALIDATE_INT) === false
            || $link->getSortOrder() < 0) {
            throw new InputException(__('Sort order must be a positive integer.'));
        }

        if ($validateLinkContent) {
            $this->validateLinkResource($link);
        }
        if ($validateSampleContent) {
            $this->validateSampleResource($link);
        }
        return true;
    }

    /**
     * Validate link resource (file or URL)
     *
     * @param LinkInterface $link
     * @throws InputException
     * @return void
     */
    protected function validateLinkResource(LinkInterface $link)
    {
        if ($link->getLinkType() == 'url'
            && !$this->urlValidator->isValid($link->getLinkUrl())
        ) {
            throw new InputException(__('Link URL must have valid format.'));
        }
        if ($link->getLinkType() == 'file'
            && (!$link->getLinkFileContent()
                || !$this->fileContentValidator->isValid($link->getLinkFileContent()))
        ) {
            throw new InputException(__('Provided file content must be valid base64 encoded data.'));
        }
    }

    /**
     * Validate sample resource (file or URL)
     *
     * @param LinkInterface $link
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(LinkInterface $link)
    {
        if ($link->getSampleType() == 'url'
            && !$this->urlValidator->isValid($link->getSampleUrl())
        ) {
            throw new InputException(__('Sample URL must have valid format.'));
        }
        if ($link->getSampleType() == 'file'
            && (!$link->getSampleFileContent()
                || !$this->fileContentValidator->isValid($link->getSampleFileContent()))
        ) {
            throw new InputException(__('Provided file content must be valid base64 encoded data.'));
        }
    }
}
