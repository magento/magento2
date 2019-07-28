<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\UpToDateValidatorInterface;

/**
 * Allows to validate if schema is up to date or not
 */
class UpToDateDeclarativeSchema implements UpToDateValidatorInterface
{
    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * UpToDateSchema constructor.
     * @param SchemaConfigInterface $schemaConfig
     * @param SchemaDiff $schemaDiff
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        SchemaDiff $schemaDiff
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->schemaDiff = $schemaDiff;
    }

    /**
     * @return string
     */
    public function getNotUpToDateMessage() : string
    {
        return 'Declarative Schema is not up to date';
    }

    /**
     * @return bool
     */
    public function isUpToDate() : bool
    {
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $dbSchema = $this->schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $dbSchema);
        return empty($diff->getAll());
    }
}
