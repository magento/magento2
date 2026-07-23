<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Declaration;

use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * This class is responsible for basic validation rules.
 *
 * @api
 */
interface ValidationInterface
{
    /**
     * Do different validations on db schema.
     *
     * @param  Schema $schema
     * @return array Return array of errors. If everything is ok - retrieve empty array
     */
    public function validate(Schema $schema);
}
