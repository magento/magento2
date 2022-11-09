<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export\Entity;

use Magento\ImportExport\Api\Data\LocalizedExportInfoInterface;

/**
 * Class ExportInfo implementation for ExportInfoInterface.
 */
class ExportInfo implements LocalizedExportInfoInterface
{
    /**
     * @var string
     */
    private $fileFormat;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var mixed
     */
    private $exportFilter;

    /**
     * @var mixed
     */
    private $skipAttr;

    /**
     * @var string
     */
    private $locale;

    /**
     * @inheritdoc
     */
    public function getFileFormat()
    {
        return $this->fileFormat;
    }

    /**
     * @inheritdoc
     */
    public function setFileFormat($fileFormat)
    {
        $this->fileFormat = $fileFormat;
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @inheritdoc
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @inheritdoc
     */
    public function getExportFilter()
    {
        return $this->exportFilter;
    }

    /**
     * @inheritdoc
     */
    public function setExportFilter($exportFilter)
    {
        $this->exportFilter = $exportFilter;
    }

    /**
     * @inheritdoc
     */
    public function getSkipAttr()
    {
        return $this->skipAttr;
    }

    /**
     * Set skipped attributes
     *
     * @param string $skipAttr
     * @return void
     */
    public function setSkipAttr($skipAttr)
    {
        $this->skipAttr = $skipAttr;
    }

    /**
     * @inheritdoc
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
