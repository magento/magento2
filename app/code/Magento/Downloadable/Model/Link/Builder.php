<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Copy;

/**
 * Class Builder
 * @api
 */
class Builder
{
    /**
     * @var Link
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
     * @var LinkFactory
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
     * @param LinkFactory $componentFactory
     */
    public function __construct(
        File $downloadableFile,
        Copy $objectCopyService,
        DataObjectHelper $dataObjectHelper,
        LinkFactory $componentFactory
    ) {
        $this->downloadableFile = $downloadableFile;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param \Magento\Downloadable\Api\Data\LinkInterface $link
     * @return \Magento\Downloadable\Api\Data\LinkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(\Magento\Downloadable\Api\Data\LinkInterface $link)
    {
        $downloadableData = $this->objectCopyService->getDataFromFieldset(
            'downloadable_data',
            'to_link',
            $this->data
        );
        $this->dataObjectHelper->populateWithArray(
            $link,
            array_merge(
                $this->data,
                $downloadableData
            ),
            \Magento\Downloadable\Api\Data\LinkInterface::class
        );
        if ($link->getLinkType() === \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
            if (!isset($this->data['file'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Link file not provided'));
            }
            $linkFileName = $this->downloadableFile->moveFileFromTmp(
                $this->getComponent()->getBaseTmpPath(),
                $this->getComponent()->getBasePath(),
                $this->data['file']
            );
            $link->setLinkFile($linkFileName);
            $link->setLinkUrl(null);
        }
        
        if (isset($this->data['sample'])) {
            $link = $this->buildSample($link, $this->data['sample']);
        }

        if (!$link->getSortOrder()) {
            $link->setSortOrder(1);
        }

        if (!is_numeric($link->getPrice())) {
            $link->setPrice(0);
        }

        if (isset($this->data['is_unlimited']) && $this->data['is_unlimited']) {
            $link->setNumberOfDownloads(0);
        }
        $this->resetData();

        return $link;
    }

    /**
     * @return void
     */
    private function resetData()
    {
        $this->data = [];
    }

    /**
     * @return Link
     */
    private function getComponent()
    {
        if (!$this->component) {
            $this->component = $this->componentFactory->create();
        }
        return $this->component;
    }

    /**
     * @param \Magento\Downloadable\Api\Data\LinkInterface $link
     * @param array $sample
     * @return \Magento\Downloadable\Api\Data\LinkInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function buildSample(\Magento\Downloadable\Api\Data\LinkInterface $link, array $sample)
    {
        if (!empty($sample['url']) || !empty($sample['file'])) {
            $downloadableLinkSampleData = $this->objectCopyService->getDataFromFieldset(
                'downloadable_link_sample_data',
                'to_link_sample',
                $this->data['sample']
            );
            $this->dataObjectHelper->populateWithArray(
                $link,
                array_merge(
                    $this->data,
                    $downloadableLinkSampleData
                ),
                \Magento\Downloadable\Api\Data\LinkInterface::class
            );
            if ($link->getSampleType() === \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                $linkSampleFileName = $this->downloadableFile->moveFileFromTmp(
                    $this->getComponent()->getBaseSampleTmpPath(),
                    $this->getComponent()->getBaseSamplePath(),
                    $sample['file']
                );
                $link->setSampleFile($linkSampleFileName);
            }
        }

        return $link;
    }
}
