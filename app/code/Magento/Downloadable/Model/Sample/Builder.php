<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Sample;

use Magento\Downloadable\Api\Data\SampleInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;

/**
 * Class Builder
 * @api
 * @since 100.1.0
 */
class Builder
{
    /**
     * @var Sample
     */
    private $component;
    
    /**
     * @var File
     */
    private $downloadableFile;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var SampleFactory
     */
    private $componentFactory;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Mapper constructor.
     *
     * @param File $downloadableFile
     * @param Copy $objectCopyService
     * @param DataObjectHelper $dataObjectHelper
     * @param SampleFactory $componentFactory
     */
    public function __construct(
        File $downloadableFile,
        Copy $objectCopyService,
        DataObjectHelper $dataObjectHelper,
        SampleFactory $componentFactory
    ) {
        $this->downloadableFile = $downloadableFile;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param array $data
     * @return $this;
     * @since 100.1.0
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param SampleInterface $sample
     * @return SampleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.1.0
     */
    public function build(SampleInterface $sample)
    {
        $downloadableData = $this->objectCopyService->getDataFromFieldset(
            'downloadable_data',
            'to_sample',
            $this->data
        );
        $this->dataObjectHelper->populateWithArray(
            $sample,
            array_merge(
                $this->data,
                $downloadableData
            ),
            SampleInterface::class
        );
        if ($sample->getSampleType() === \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
            if (!isset($this->data['file'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Sample file not provided'));
            }
            $fileName = $this->downloadableFile->moveFileFromTmp(
                $this->getComponent()->getBaseTmpPath(),
                $this->getComponent()->getBasePath(),
                $this->data['file']
            );
            $sample->setSampleFile($fileName);
        }
        if (!$sample->getSortOrder()) {
            $sample->setSortOrder(1);
        }
        $this->resetData();

        return $sample;
    }

    /**
     * @return void
     */
    private function resetData()
    {
        $this->data = [];
    }

    /**
     * @return Sample
     */
    private function getComponent()
    {
        if (!$this->component) {
            $this->component = $this->componentFactory->create();
        }
        return $this->component;
    }
}
