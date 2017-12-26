<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Declaration;

use Magento\Setup\Model\Declaration\Schema\Dto\Schema;

/**
 * This class is responsible for basic validation rules
 */
interface ValidationInterface
{
    /**
     * Do different validations on readed db schema
     *
     * @param  Schema $schema
     * @return array Return array of errors. If everything is ok - retrieve empty array
     */
    public function validate(Schema $schema);
}
