<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api\Data;

interface FieldsEnclosureAwareExportInfoInterface extends LocalizedExportInfoInterface
{
    /**
     * Returns whether fields enclosure is enabled
     *
     * @return bool|null
     */
    public function getFieldsEnclosure(): ?bool;

    /**
     * Set whether fields enclosure is enabled
     *
     * @param bool $fieldsEnclosure
     * @return void
     */
    public function setFieldsEnclosure(bool $fieldsEnclosure): void;
}
