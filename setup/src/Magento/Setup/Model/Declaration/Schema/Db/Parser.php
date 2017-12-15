<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Dto\Structure;
use Magento\Setup\Model\Declaration\Schema\SchemaParserInterface;

/**
 * Parser is responsible for builind structure.
 * @see Structure
 *
 * @inheritdoc
 */
class Parser implements SchemaParserInterface
{
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;

    /**
     * @param StructureBuilder $structureBuilder
     */
    public function __construct(StructureBuilder $structureBuilder)
    {
        $this->structureBuilder = $structureBuilder;
    }

    /**
     * @inheritdoc
     */
    public function parse(Structure $structure)
    {
        return $this->structureBuilder->build($structure);
    }
}
