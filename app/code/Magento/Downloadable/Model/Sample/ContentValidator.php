<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Url\Validator as UrlValidator;

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
     * Validate sample resource (file or URL)
     *
     * @param SampleInterface $sample
     * @throws InputException
     * @return void
     */
    protected function validateSampleResource(SampleInterface $sample)
    {
        $sampleFile = $sample->getSampleFileContent();
        if ($sample->getSampleType() == 'file'
            && (!$sampleFile || !$this->fileContentValidator->isValid($sampleFile))
        ) {
            throw new InputException(__('Provided file content must be valid base64 encoded data.'));
        }

        if ($sample->getSampleType() == 'url'
            && !$this->urlValidator->isValid($sample->getSampleUrl())
        ) {
            throw new InputException(__('Sample URL must have valid format.'));
        }
    }
}
