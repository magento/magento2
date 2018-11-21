<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\Declaration\Schema\Sharding;

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
            $dom = $this->processTables($schemaPatch, $tablesData);
            $this->persistModule($dom, $schemaPatch);
        }
    }

    /**
     * Convert tables data into XML document.
     *
     * @param string $schemaPatch
     * @param array $tablesData
     * @return \SimpleXMLElement
     */
    private function processTables(string $schemaPatch, array $tablesData): \SimpleXMLElement
    {
        if (file_exists($schemaPatch)) {
            $dom = new \SimpleXMLElement(file_get_contents($schemaPatch));
        } else {
            $dom = $this->initEmptyDom();
        }

        foreach ($tablesData as $tableName => $tableData) {
            $tableData = $this->handleDefinition($tableData);
            $table = $dom->addChild('table');
            $table->addAttribute('name', $tableName);
            if (!empty($tableData['disabled'])) {
                $table->addAttribute('disabled', $this->castBooleanToString((bool)$tableData['disabled']));
                continue;
            }

            $table->addAttribute('resource', $tableData['resource'] ?: Sharding::DEFAULT_CONNECTION);
            if (isset($tableData['engine']) && $tableData['engine'] !== null) {
                $table->addAttribute('engine', $tableData['engine']);
            }
            if (!empty($tableData['comment'])) {
                $table->addAttribute('comment', $tableData['comment']);
            }

            $this->processColumns($tableData, $table);
            $this->processConstraints($tableData, $table);
            $this->processIndexes($tableData, $table);
        }

        return $dom;
    }

    /**
     * If disabled attribute is set to false it remove it at all.
     *
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
        if (!isset($tableData['columns'])) {
            return $table;
        }

        foreach ($tableData['columns'] as $columnName => $columnData) {
            $columnData = $this->handleDefinition($columnData);
            $domColumn = $table->addChild('column');
            if (!empty($columnData['disabled'])) {
                $domColumn->addAttribute('name', $columnName);
                $domColumn->addAttribute('disabled', $this->castBooleanToString((bool)$columnData['disabled']));
                continue;
            }

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
                $domIndex->addAttribute('referenceId', $indexName);

                if (!empty($indexData['disabled'])) {
                    $domIndex->addAttribute('disabled', $this->castBooleanToString((bool)$indexData['disabled']));
                    continue;
                }

                $domIndex->addAttribute('indexType', $indexData['indexType']);
                foreach ($indexData['columns'] as $column) {
                    $columnXml = $domIndex->addChild('column');
                    $columnXml->addAttribute('name', $column);
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
        if (!isset($tableData['constraints'])) {
            return $table;
        }

        foreach ($tableData['constraints'] as $constraintType => $constraints) {
            foreach ($constraints as $name => $constraintData) {
                $constraintData = $this->handleDefinition($constraintData);
                $constraintDom = $table->addChild('constraint');
                $constraintDom->addAttribute('xsi:type', $constraintType, 'xsi');
                $constraintDom->addAttribute('referenceId', $name);

                if (!empty($constraintData['disabled'])) {
                    $constraintDom->addAttribute(
                        'disabled',
                        $this->castBooleanToString((bool)$constraintData['disabled'])
                    );
                    continue;
                }

                if ($constraintType === 'foreign') {
                    foreach ($constraintData as $attributeKey => $attributeValue) {
                        $constraintDom->addAttribute($attributeKey, $attributeValue);
                    }
                } else {
                    $constraintData['columns'] = $constraintData['columns'] ?? [];
                    foreach ($constraintData['columns'] as $column) {
                        $columnXml = $constraintDom->addChild('column');
                        $columnXml->addAttribute('name', $column);
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
