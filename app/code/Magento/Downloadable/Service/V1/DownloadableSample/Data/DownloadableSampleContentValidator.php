<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample\Data;

use Magento\Downloadable\Service\V1\Data\FileContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Url\Validator as UrlValidator;

class DownloadableSampleContentValidator
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
     * Check if sample content is valid
     *
     * @param DownloadableSampleContent $sampleContent
     * @return bool
     * @throws InputException
     */
    public function isValid(DownloadableSampleContent $sampleContent)
    {
        if (!is_int($sampleContent->getSortOrder()) || $sampleContent->getSortOrder() < 0) {
            throw new InputException('Sort order must be a positive integer.');
        }

        $this->validateSampleResource($sampleContent);
        return true;
    }

    /**
     * Validate sample resource (file or URL)
     *
     * @param DownloadableSampleContent $sampleContent
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(DownloadableSampleContent $sampleContent)
    {
        $sampleFile = $sampleContent->getSampleFile();
        if ($sampleContent->getSampleType() == 'file'
            && (!$sampleFile || !$this->fileContentValidator->isValid($sampleFile))
        ) {
            throw new InputException('Provided file content must be valid base64 encoded data.');
        }

        if ($sampleContent->getSampleType() == 'url'
            && !$this->urlValidator->isValid($sampleContent->getSampleUrl())
        ) {
            throw new InputException('Sample URL must have valid format.');
        }
    }
}
