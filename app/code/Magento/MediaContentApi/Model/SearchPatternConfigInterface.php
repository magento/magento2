<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model;

/**
 * Interface for Media content Config.
 * @api
 */
interface SearchPatternConfigInterface
{
    /**
     * Retrieve search RegExp patterns for finding media asset paths within content
     *
     * @return array
     */
    public function get(): array;
}
