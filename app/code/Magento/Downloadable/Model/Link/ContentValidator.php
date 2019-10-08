<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Url\Validator as UrlValidator;

/**
 * Class to validate Link Content.
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
     * @var File
     */
    private $fileHelper;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * @param FileContentValidator $fileContentValidator
     * @param UrlValidator $urlValidator
     * @param File|null $fileHelper
     * @param DomainValidator|null $domainValidator
     */
    public function __construct(
        FileContentValidator $fileContentValidator,
        UrlValidator $urlValidator,
        File $fileHelper = null,
        DomainValidator $domainValidator = null
    ) {
        $this->fileContentValidator = $fileContentValidator;
        $this->urlValidator = $urlValidator;
        $this->fileHelper = $fileHelper ?? ObjectManager::getInstance()->get(File::class);
        $this->domainValidator = $domainValidator ?? ObjectManager::getInstance()->get(DomainValidator::class);
    }

    /**
     * Check if link content is valid.
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
     * Validate link resource (file or URL).
     *
     * @param LinkInterface $link
     * @return void
     * @throws InputException
     */
    protected function validateLinkResource(LinkInterface $link)
    {
        if ($link->getLinkType() === 'url') {
            if (!$this->urlValidator->isValid($link->getLinkUrl())) {
                throw new InputException(__('Link URL must have valid format.'));
            }
            if (!$this->domainValidator->isValid($link->getLinkUrl())) {
                throw new InputException(__('Link URL\'s domain is not in list of downloadable_domains in env.php.'));
            }
        } elseif ($link->getLinkFileContent()) {
            if (!$this->fileContentValidator->isValid($link->getLinkFileContent())) {
                throw new InputException(__('Provided file content must be valid base64 encoded data.'));
            }
        } elseif (!$this->isFileValid($link->getBasePath() . $link->getLinkFile())) {
            throw new InputException(__('Link file not found. Please try again.'));
        }
    }

    /**
     * Validate sample resource (file or URL).
     *
     * @param LinkInterface $link
     * @return void
     * @throws InputException
     */
    protected function validateSampleResource(LinkInterface $link)
    {
        if ($link->getSampleType() === 'url') {
            if (!$this->urlValidator->isValid($link->getSampleUrl())) {
                throw new InputException(__('Sample URL must have valid format.'));
            }
            if (!$this->domainValidator->isValid($link->getSampleUrl())) {
                throw new InputException(__('Sample URL\'s domain is not in list of downloadable_domains in env.php.'));
            }
        } elseif ($link->getSampleFileContent()) {
            if (!$this->fileContentValidator->isValid($link->getSampleFileContent())) {
                throw new InputException(__('Provided file content must be valid base64 encoded data.'));
            }
        } elseif (!$this->isFileValid($link->getBaseSamplePath() . $link->getSampleFile())) {
            throw new InputException(__('Link sample file not found. Please try again.'));
        }
    }

    /**
     * Check that Links File or Sample is valid.
     *
     * @param string $file
     * @return bool
     */
    private function isFileValid(string $file): bool
    {
        try {
            return $this->fileHelper->ensureFileInFilesystem($file);
        } catch (ValidatorException $e) {
            return false;
        }
    }
}
