<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api\Data;

/**
 * Basic interface with data needed for export operation.
 * @api
 */
interface ExportInfoInterface
{
    /**
     * Return filename.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set filename into local variable.
     *
     * @param string $fileName
     * @return void
     */
    public function setFileName($fileName);

    /**
     * Override standard entity getter.
     *
     * @return string
     */
    public function getFileFormat();

    /**
     * Set file format.
     *
     * @param string $fileFormat
     * @return void
     */
    public function setFileFormat($fileFormat);

    /**
     * Return content type.
     *
     * @return string
     */
    public function getContentType();

    /**
     * Set content type.
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType);

    /**
     * Returns entity.
     *
     * @return string
     */
    public function getEntity();

    /**
     * Set entity for export logic.
     *
     * @param string $entity
     * @return void
     */
    public function setEntity($entity);

    /**
     * Returns export filter.
     *
     * @return string
     */
    public function getExportFilter();

    /**
     * Set filter for export result.
     *
     * @param string $exportFilter
     * @return void
     */
    public function setExportFilter($exportFilter);
}
