<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Generate\Fixture;

use Magento\Framework\ObjectManagerInterface;

/**
 * Fixture files generator.
 */
class SchemaXml
{
    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Provider of fields from database.
     *
     * @var FieldsProvider
     */
    protected $fieldsProvider;

    /**
     * The DOMDocument class represents an entire XML.
     *
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * Required fields list.
     *
     * @var array
     */
    protected $requiredFields = [
        'name',
        'entity_type',
        'collection',
    ];

    /**
     * @constructor
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        $this->fieldsProvider = $this->objectManager->create(\Magento\Mtf\Util\Generate\Fixture\FieldsProvider::class);
        $this->dom = new \DOMDocument('1.0');
        $this->dom->load(dirname(__FILE__) . '/template.xml');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
    }

    /**
     * Launch Fixture generators.
     *
     * @return void
     */
    public function launch()
    {
        $options = getopt('', ['type:', 'name:', 'entity_type:', 'collection:', 'help']);
        $checkKeyExists = count(array_diff($this->requiredFields, array_keys($options)));

        if (empty($options) || isset($options['help']) || $checkKeyExists > 0) {
            $this->getHelp();
        }
        $config['type'] = empty($options['type']) ? 'flat' : $options['type'];
        if ($config['type'] === 'composite') {
            $options['entities'] = explode(',', $options['entity_type']);
            unset($options['entity_type']);
        }
        $config = array_merge($config, $options);

        $this->generate($config);
    }

    /**
     * Generate Fixtures XML.
     *
     * @param array $config
     * @return void
     */
    public function generate(array $config)
    {
        if (!$this->fieldsProvider->checkConnection()) {
            return;
        }

        $this->generateFixtureXml($config);
    }

    /**
     * Generate fixtures XML definition files.
     *
     * @param array $config
     * @return void
     */
    protected function generateFixtureXml(array $config)
    {
        $classShortName = ucfirst($config['name']);
        $fileName = $classShortName . '.xml';
        $collection = explode('\\', $config['collection']);
        $collection = array_values(array_filter($collection));
        $path = $collection[0] . '\\' . $collection[1] . '\Test\Fixture\\';
        $module = $collection[0] . '_' . $collection[1];
        $repositoryClass = $collection[0] . '\\' . $collection[1] . '\Test\Repository\\' . $classShortName;
        $handlerInterface = $collection[0] . '\\' . $collection[1] . '\Test\Handler\\';
        $handlerInterface .= $classShortName . '\\' . $classShortName . 'Interface';
        $fixtureClass = $path . $classShortName;
        $folderName = MTF_TESTS_PATH . $path;
        $pathToFile = str_replace('\\', DIRECTORY_SEPARATOR, $folderName . $fileName);
        if (file_exists($pathToFile)) {
            echo "Fixture with name ($pathToFile) already exists.\n";
            return;
        }
        if (!is_dir($folderName)) {
            mkdir($folderName, 0777, true);
        }

        /** @var \DOMElement $root */
        $root = $this->dom->getElementsByTagName('config')->item(0);

        $fixture = $this->dom->createElement('fixture');
        $fixture->setAttribute('name', $config['name']);
        $fixture->setAttribute('module', $module);
        $fixture->setAttribute('type', $config['type']);
        $fixture->setAttribute('collection', implode('\\', $collection));
        $fixture->setAttribute('repository_class', $repositoryClass);
        $fixture->setAttribute('handler_interface', $handlerInterface);
        $fixture->setAttribute('class', $fixtureClass);
        if (isset($config['entity_type'])) {
            $fixture->setAttribute('entity_type', $config['entity_type']);
        }
        $root->appendChild($fixture);

        $fields = $this->fieldsProvider->getFields($config);
        foreach ($fields as $fieldName => $fieldValue) {
            $field = $this->dom->createElement('field');
            $field->setAttribute('name', $fieldName);
            $field->setAttribute('is_required', intval($fieldValue['is_required']));
            $fixture->appendChild($field);
        }

        file_put_contents($pathToFile, str_replace('  ', '    ', $this->dom->saveXML()));
    }

    /**
     * Prints help info and stops code execution.
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function getHelp()
    {
        echo <<<TAG
Usage: Magento 2 fixture schema generator.

 --type\t\t<flat>|<eav>|<table>|<composite>\t\tTable type for the entity\tDefault: flat
 --name\t\t<className>\t\t\t\t\tName of generated class
 --entity_type\t<entity_type>|<entity_type1,entity_type2>\tDatabase table name where entity data is stored
 --collection\t<path\\\\to\\\\collection>\t\t\t\tCollection to generate data sets\tNOTE: All backslashes must be escaped
 --help\t\tThis help

 name, entity_type, collection - required fields

TAG;
        exit(0);
    }
}
