<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Shell;

/**
 * Persist listened schema to db_schema.xml file.
 */
class SchemaPersistor
{
    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var XmlPersistor
     */
    private $xmlPersistor;

    /**
     * Constructor.
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param XmlPersistor $xmlPersistor
     */
    public function __construct(ComponentRegistrar $componentRegistrar, XmlPersistor $xmlPersistor)
    {
        $this->componentRegistrar = $componentRegistrar;
        $this->xmlPersistor = $xmlPersistor;
    }

    /**
     * Initialize bare DOM XML element.
     *
     * @return \SimpleXMLElement
     */
    private function initEmptyDom()
    {
        return new \SimpleXMLElement(
            '<schema 
                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">
            </schema>'
        );
    }

    /**
     * Do persist by modules to db_schema.xml file.
     *
     * @param SchemaListener $schemaListener
     */
    public function persist(SchemaListener $schemaListener)
    {
        foreach ($schemaListener->getTables() as $moduleName => $tablesData) {
            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
            if (empty($path)) {
                /** Empty path means that module does not exist */
                continue;
            }
            $schemaPatch = sprintf('%s/etc/db_schema.xml', $path);
            if (file_exists($schemaPatch)) {
                $dom = new \SimpleXMLElement(file_get_contents($schemaPatch));
            } else {
                $dom = $this->initEmptyDom();
            }

            foreach ($tablesData as $tableName => $tableData) {
                $tableData = $this->handleDefinition($tableData);
                $table = $dom->addChild('table');
                $table->addAttribute('name', $tableName);
                $table->addAttribute('resource', $tableData['resource']);
                if (isset($tableData['engine']) && $tableData['engine'] !== null) {
                    $table->addAttribute('engine', $tableData['engine']);
                }

                $this->processColumns($tableData, $table);
                $this->processConstraints($tableData, $table);
                $this->processIndexes($tableData, $table);
            }

            $this->persistModule($dom, $schemaPatch);
        }
    }

    /**
     * If disabled attribute is set to false it remove it at all.
     * Also handle other generic attributes.
     *
     * @param array $definition
     * @return array
     */
    private function handleDefinition(array $definition)
    {
        if (isset($definition['disabled']) && !$definition['disabled']) {
            unset($definition['disabled']);
        }

        return $definition;
    }

    /**
     * Cast boolean types to string.
     *
     * @param bool $boolean
     * @return string
     */
    private function castBooleanToString($boolean)
    {
        return $boolean ? 'true' : 'false';
    }

    /**
     * Convert columns from array to XML format.
     *
     * @param array $tableData
     * @param \SimpleXMLElement $table
     * @return \SimpleXMLElement
     */
    private function processColumns(array $tableData, \SimpleXMLElement $table)
    {
        if (isset($tableData['columns'])) {
            foreach ($tableData['columns'] as $columnData) {
                $columnData = $this->handleDefinition($columnData);
                $domColumn = $table->addChild('column');
                $domColumn->addAttribute('xsi:type', $columnData['xsi:type'], 'xsi');
                unset($columnData['xsi:type']);

                foreach ($columnData as $attributeKey => $attributeValue) {
                    if ($attributeValue === null) {
                        continue;
                    }

                    if (is_bool($attributeValue)) {
                        $attributeValue = $this->castBooleanToString($attributeValue);
                    }

                    $domColumn->addAttribute($attributeKey, $attributeValue);
                }
            }
        }

        return $table;
    }

    /**
     * Convert columns from array to XML format.
     *
     * @param array $tableData
     * @param \SimpleXMLElement $table
     * @return \SimpleXMLElement
     */
    private function processIndexes(array $tableData, \SimpleXMLElement $table)
    {
        if (isset($tableData['indexes'])) {
            foreach ($tableData['indexes'] as $indexName => $indexData) {
                $indexData = $this->handleDefinition($indexData);
                $domIndex = $table->addChild('index');
                $domIndex->addAttribute('name', $indexName);

                if (isset($indexData['disabled']) && $indexData['disabled']) {
                    $domIndex->addAttribute('disabled', true);
                } else {
                    $domIndex->addAttribute('indexType', $indexData['indexType']);

                    foreach ($indexData['columns'] as $column) {
                        $columnXml = $domIndex->addChild('column');
                        $columnXml->addAttribute('name', $column);
                    }
                }
            }
        }

        return $table;
    }

    /**
     * Convert constraints from array to XML format.
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
                    foreach ($constraints as $name => $constraintData) {
                        $constraintData = $this->handleDefinition($constraintData);
                        $constraintDom = $table->addChild('constraint');
                        $constraintDom->addAttribute('xsi:type', $constraintType, 'xsi');
                        $constraintDom->addAttribute('name', $name);

                        foreach ($constraintData as $attributeKey => $attributeValue) {
                            $constraintDom->addAttribute($attributeKey, $attributeValue);
                        }
                    }
                } else {
                    foreach ($constraints as $name => $constraintData) {
                        $constraintData = $this->handleDefinition($constraintData);
                        $constraintDom = $table->addChild('constraint');
                        $constraintDom->addAttribute('xsi:type', $constraintType, 'xsi');
                        $constraintDom->addAttribute('name', $name);
                        $constraintData['columns'] = $constraintData['columns'] ?? [];

                        if (isset($constraintData['disabled'])) {
                            $constraintDom->addAttribute('disabled', (bool) $constraintData['disabled']);
                        }

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
     * Do schema persistence to specific module.
     *
     * @param \SimpleXMLElement $simpleXmlElementDom
     * @param string $path
     * @return void
     */
    private function persistModule(\SimpleXMLElement $simpleXmlElementDom, $path)
    {
        $this->xmlPersistor->persist($simpleXmlElementDom, $path);
    }
}
