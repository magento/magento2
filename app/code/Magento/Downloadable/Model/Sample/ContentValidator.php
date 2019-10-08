<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Url\Validator as UrlValidator;

/**
 * Class to validate Sample Content.
 */
class ContentValidator
{
    /**
     * @var UrlValidator
     */
    protected $urlValidator;

    /**
     * @var FileContentValidator
     */
    protected $fileContentValidator;

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
     * Check if sample content is valid.
     *
     * @param SampleInterface $sample
     * @param bool $validateSampleContent
     * @return bool
     * @throws InputException
     */
    public function isValid(SampleInterface $sample, $validateSampleContent = true)
    {
        if (filter_var($sample->getSortOrder(), FILTER_VALIDATE_INT) === false || $sample->getSortOrder() < 0) {
            throw new InputException(__('Sort order must be a positive integer.'));
        }

        if ($validateSampleContent) {
            $this->validateSampleResource($sample);
        }

        return true;
    }

    /**
     * Validate sample resource (file or URL).
     *
     * @param SampleInterface $sample
     * @return void
     * @throws InputException
     */
    protected function validateSampleResource(SampleInterface $sample)
    {
        if ($sample->getSampleType() === 'url') {
            if (!$this->urlValidator->isValid($sample->getSampleUrl())) {
                throw new InputException(__('Sample URL must have valid format.'));
            }
            if (!$this->domainValidator->isValid($sample->getSampleUrl())) {
                throw new InputException(__('Sample URL\'s domain is not in list of downloadable_domains in env.php.'));
            }
        } elseif ($sample->getSampleFileContent()) {
            if (!$this->fileContentValidator->isValid($sample->getSampleFileContent())) {
                throw new InputException(__('Provided file content must be valid base64 encoded data.'));
            }
        } elseif (!$this->isFileValid($sample->getBasePath() . $sample->getSampleFile())) {
            throw new InputException(__('Sample file not found. Please try again.'));
        }
    }

    /**
     * Check that Samples file is valid.
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
