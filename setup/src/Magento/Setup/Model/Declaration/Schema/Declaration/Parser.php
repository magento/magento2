<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Declaration;

use Magento\Setup\Model\Declaration\Schema\Dto\Structure;
use Magento\Setup\Model\Declaration\Schema\FileSystem\Reader;
use Magento\Setup\Model\Declaration\Schema\SchemaParserInterface;

/**
 * Read schema data from XML and convert it to objects representation
 */
class Parser implements SchemaParserInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var StructureBuilder
     */
    private $structureBuilder;

    /**
     * @var DynamicStructureInterface
     */
    private $dynamicStructure;

    /**
     * @param Reader $reader
     * @param StructureBuilder $structureBuilder
     * @param DynamicStructureInterface $dynamicStructure
     */
    public function __construct(
        Reader $reader,
        StructureBuilder $structureBuilder,
        DynamicStructureInterface $dynamicStructure
    ) {
        $this->reader = $reader;
        $this->structureBuilder = $structureBuilder;
        $this->dynamicStructure = $dynamicStructure;
    }

    /**
     * Convert XML to object representation
     *
     * @param Structure $structure
     * @return Structure
     */
    public function parse(Structure $structure)
    {
        $info = $this->reader->read();
        $info = array_replace_recursive($info, $this->dynamicStructure->getStructure());
        $this->structureBuilder->addTablesData($info['table']);
        $structure = $this->structureBuilder->build($structure);
        return $structure;
    }
}
