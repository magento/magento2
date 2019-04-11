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
        $defaultAttributesValues = [
            'resource' => Sharding::DEFAULT_CONNECTION,
        ];

        foreach ($tablesData as $tableName => $tableData) {
            $tableData = $this->handleDefinition($tableData);
            $table = $dom->xpath("//table[@name='" . $tableName . "']");
            if (!$table) {
                $table = $dom->addChild('table');
                $table->addAttribute('name', $tableName);
            } else {
                $table = reset($table);
            }

            $attributeNames = ['disabled', 'resource', 'engine', 'comment'];
            foreach ($attributeNames as $attributeName) {
                $this->updateElementAttribute(
                    $table,
                    $attributeName,
                    $tableData,
                    $defaultAttributesValues[$attributeName] ?? null
                );
            }

            $this->processColumns($tableData, $table);
            $this->processConstraints($tableData, $table);
            $this->processIndexes($tableData, $table);
        }

        return $dom;
    }

    /**
     * Update element attribute value or create new attribute.
     *
     * @param \SimpleXMLElement $element
     * @param string $attributeName
     * @param array $elementData
     * @param string|null $defaultValue
     */
    private function updateElementAttribute(
        \SimpleXMLElement $element,
        string $attributeName,
        array $elementData,
        ?string $defaultValue = null
    ) {
        $attributeValue = $elementData[$attributeName] ?? $defaultValue;
        if ($attributeValue !== null) {
            if (is_bool($attributeValue)) {
                $attributeValue = $this->castBooleanToString($attributeValue);
            }

            if ($element->attributes()[$attributeName]) {
                $element->attributes()->$attributeName = $attributeValue;
            } else {
                $element->addAttribute($attributeName, $attributeValue);
            }
        }
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
            $domColumn = $table->xpath("column[@name='" . $columnName . "']");
            if (!$domColumn) {
                $domColumn = $table->addChild('column');
                if (!empty($columnData['xsi:type'])) {
                    $domColumn->addAttribute('xsi:type', $columnData['xsi:type'], 'xsi');
                }
                $domColumn->addAttribute('name', $columnName);
            } else {
                $domColumn = reset($domColumn);
            }

            $attributeNames = array_diff(array_keys($columnData), ['name', 'xsi:type']);
            foreach ($attributeNames as $attributeName) {
                $this->updateElementAttribute(
                    $domColumn,
                    $attributeName,
                    $columnData
                );
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

                $domIndex = $table->xpath("index[@referenceId='" . $indexName . "']");
                if (!$domIndex) {
                    $domIndex = $this->getUniqueIndexByName($table, $indexName);
                }

                if (!$domIndex) {
                    $domIndex = $table->addChild('index');
                    $domIndex->addAttribute('referenceId', $indexName);
                } elseif (is_array($domIndex)) {
                    $domIndex = reset($domIndex);
                }

                $attributeNames = array_diff(array_keys($indexData), ['referenceId', 'columns', 'name']);
                foreach ($attributeNames as $attributeName) {
                    $this->updateElementAttribute(
                        $domIndex,
                        $attributeName,
                        $indexData
                    );
                }
                
                if (!empty($indexData['columns'])) {
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
        if (!isset($tableData['constraints'])) {
            return $table;
        }

        foreach ($tableData['constraints'] as $constraintType => $constraints) {
            foreach ($constraints as $constraintName => $constraintData) {
                $constraintData = $this->handleDefinition($constraintData);
                $domConstraint = $table->xpath("constraint[@referenceId='" . $constraintName . "']");
                if (!$domConstraint) {
                    $domConstraint = $table->addChild('constraint');
                    $domConstraint->addAttribute('xsi:type', $constraintType, 'xsi');
                    $domConstraint->addAttribute('referenceId', $constraintName);
                } else {
                    $domConstraint = reset($domConstraint);
                }

                $attributeNames = array_diff(
                    array_keys($constraintData),
                    ['referenceId', 'xsi:type', 'disabled', 'columns', 'name', 'type']
                );
                foreach ($attributeNames as $attributeName) {
                    $this->updateElementAttribute(
                        $domConstraint,
                        $attributeName,
                        $constraintData
                    );
                }

                if (!empty($constraintData['columns'])) {
                    foreach ($constraintData['columns'] as $column) {
                        $columnXml = $domConstraint->addChild('column');
                        $columnXml->addAttribute('name', $column);
                    }
                }

                if (!empty($constraintData['disabled'])) {
                    $this->updateElementAttribute(
                        $domConstraint,
                        'disabled',
                        $constraintData
                    );
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

    /**
     * Retrieve unique index declaration by name.
     *
     * @param \SimpleXMLElement $table
     * @param string $indexName
     * @return \SimpleXMLElement|null
     */
    private function getUniqueIndexByName(\SimpleXMLElement $table, string $indexName): ?\SimpleXMLElement
    {
        $indexElement = null;
        $constraint = $table->xpath("constraint[@referenceId='" . $indexName . "']");
        if ($constraint) {
            $constraint = reset($constraint);
            $type = $constraint->attributes('xsi', true)->type;
            if ($type == 'unique') {
                $indexElement = $constraint;
            }
        }

        return $indexElement;
    }
}
