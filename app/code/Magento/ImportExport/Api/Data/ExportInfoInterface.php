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
 * @since 100.3.2
 */
interface ExportInfoInterface
{
    /**
     * Return filename.
     *
     * @return string
     * @since 100.3.2
     */
    public function getFileName();

    /**
     * Set filename into local variable.
     *
     * @param string $fileName
     * @return void
     * @since 100.3.2
     */
    public function setFileName($fileName);

    /**
     * Override standard entity getter.
     *
     * @return string
     * @since 100.3.2
     */
    public function getFileFormat();

    /**
     * Set file format.
     *
     * @param string $fileFormat
     * @return void
     * @since 100.3.2
     */
    public function setFileFormat($fileFormat);

    /**
     * Return content type.
     *
     * @return string
     * @since 100.3.2
     */
    public function getContentType();

    /**
     * Set content type.
     *
     * @param string $contentType
     * @return void
     * @since 100.3.2
     */
    public function setContentType($contentType);

    /**
     * Returns entity.
     *
     * @return string
     * @since 100.3.2
     */
    public function getEntity();

    /**
     * Set entity for export logic.
     *
     * @param string $entity
     * @return void
     * @since 100.3.2
     */
    public function setEntity($entity);

    /**
     * Returns export filter.
     *
     * @return string
     * @since 100.3.2
     */
    public function getExportFilter();

    /**
     * Set filter for export result.
     *
     * @param string $exportFilter
     * @return void
     * @since 100.3.2
     */
    public function setExportFilter($exportFilter);
}
