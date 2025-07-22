<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
