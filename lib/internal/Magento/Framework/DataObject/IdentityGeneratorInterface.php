<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DataObject;

/**
 * Interface Identity Generator
 *
 * @api
 */
interface IdentityGeneratorInterface
{
    /**
     * Generate id
     *
     * @return string
     **/
    public function generateId();

    /**
     * Generate id for data
     *
     * @param string $data
     * @return string
     **/
    public function generateIdForData($data);
}
