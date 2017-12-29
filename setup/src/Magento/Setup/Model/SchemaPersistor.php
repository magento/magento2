<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Persist listened schema to db_schema.xml file
 */
class SchemaPersistor
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @return \SimpleXMLElement
     */
    private function initEmptyDom()
    {
        return new \SimpleXMLElement(
            '<schema 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:noNamespaceSchemaLocation="urn:magento:setup:Model/Declaration/Schema/etc/schema.xsd"></schema>
                ');
    }

    /**
     * Do persist by modules to db_schema.xml file
     *
     * @param SchemaListener $schemaListener
     */
    public function persist(SchemaListener $schemaListener)
    {
        foreach ($schemaListener->getTables() as $moduleName => $tablesData) {
            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            $schemaPatch = sprintf('%s/etc/db_schema.xml', $path);
            if (file_exists($schemaPatch)) {
                $dom = new \SimpleXMLElement(file_get_contents($schemaPatch));
            } else {
                $dom = $this->initEmptyDom();
            }

            foreach ($tablesData as $tableName => $tableData) {
                $table = $dom->addChild('table');
                $table->addAttribute('name', $tableName);
                /** @TODO: handle different resources for sales and checkout tables */
                $table->addAttribute('resource', 'default');
                $this->processColumns($tableData, $table);
                $this->processConstraints($tableData, $table);
                $this->processIndexes($tableData, $table);
            }

            $this->persistModule($dom, $schemaPatch);
        }
    }

    /**
     * Convert columns from array to XML format
     *
     * @param array $tableData
     * @param \SimpleXMLElement $table
     * @return \SimpleXMLElement
     */
    private function processColumns(array $tableData, \SimpleXMLElement $table)
    {
        if (isset($tableData['columns'])) {
            foreach ($tableData['columns'] as $columnData) {
                $domColumn = $table->addChild('column');
                $domColumn->addAttribute('xsi:type', $columnData['xsi:type'], 'xsi');
                unset($columnData['xsi:type']);

                foreach ($columnData as $attributeKey => $attributeValue) {
                    $domColumn->addAttribute($attributeKey, $attributeValue);
                }
            }
        }

        return $table;
    }

    /**
     * Convert columns from array to XML format
     *
     * @param array $tableData
     * @param \SimpleXMLElement $table
     * @return \SimpleXMLElement
     */
    private function processIndexes(array $tableData, \SimpleXMLElement $table)
    {
        if (isset($tableData['indexes'])) {
            foreach ($tableData['indexes'] as $indexName => $indexData) {
                $domIndex = $table->addChild('index');
                $domIndex->addAttribute('name', $indexName);
                $domIndex->addAttribute('type', $indexData['type']);

                foreach ($indexData['columns'] as $column) {
                    $columnXml = $domIndex->addChild('column');
                    $columnXml->addAttribute('name', $column);
                }
            }
        }

        return $table;
    }

    /**
     * Convert constraints from array to XML format
     *
     * @param array $tableData
     * @param \SimpleXMLElement $table
     * @return \SimpleXMLElement
     */
    private function processConstraints(array $tableData, \SimpleXMLElement $table)
    {
        if (isset($tableData['constraints'])) {
            foreach ($tableData['constraints'] as $constraintType => $constraints) {
                if ($constraintType === 'foreign') {
                    foreach ($constraints as $constraintData) {
                        $constraintDom = $table->addChild('constraint');
                        $constraintDom->addAttribute('xsi:type', $constraintType, 'xsi');

                        foreach ($constraintData as $attributeKey => $attributeValue) {
                            $constraintDom->addAttribute($attributeKey, $attributeValue);
                        }
                    }
                } else {
                    foreach ($constraints as $name => $constraintData) {
                        $constraintDom = $table->addChild('constraint');
                        $constraintDom->addAttribute('xsi:type', $constraintType, 'xsi');
                        $constraintDom->addAttribute('name', $name);

                        foreach ($constraintData['columns'] as $column) {
                            $columnXml = $constraintDom->addChild('column');
                            $columnXml->addAttribute('name', $column);
                        }
                    }
                }
            }
        }

        return $table;
    }

    /**
     * Do schema persistence to specific module
     *
     * @param \SimpleXMLElement $simpleXmlElementDom
     * @param string $path
     * @return void
     */
    private function persistModule(\SimpleXMLElement $simpleXmlElementDom, $path)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($simpleXmlElementDom->asXML());
        file_put_contents($path, $dom->saveXML());
    }
}
