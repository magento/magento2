<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Fixture\Import;

use Magento\ImportExport\Mtf\Util\Import\File\CsvTemplate;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Generate\File\Generator;

/**
 * Fixture of file.
 */
class File extends DataSource
{
    /**
     * Website code mapping.
     *
     * @var array
     */
    private $mainWebsiteMapping;

    /**
     * Fixture data.
     *
     * @var array
     */
    private $value;

    /**
     * Template of csv file.
     *
     * @var array
     */
    private $csvTemplate;

    /**
     * Generator for the uploaded file.
     *
     * @var Generator
     */
    private $generator;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Entities fixtures.
     *
     * @var FixtureInterface[]
     */
    private $entities;

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Csv as array.
     *
     * @var array
     */
    private $csv;

    /**
     * Placeholders.
     *
     * @var array
     */
    private $placeholders;

    /**
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param Generator $generator
     * @param array $params
     * @param array|string $data
     */
    public function __construct(
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        Generator $generator,
        array $params,
        $data = []
    ) {
        $this->params = $params;
        $this->value = $data;
        $this->generator = $generator;
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = null)
    {
        if (isset($this->data)) {
            return parent::getData($key);
        }

        $filename = MTF_TESTS_PATH . $this->value['template']['filename'] . '.php';
        if (!file_exists($filename)) {
            throw new \Exception("CSV file '{$filename}'' not found on the server.");
        }

        $this->csvTemplate = include $filename;

        $this->placeholders = [];
        if (!isset($this->products)
            && isset($this->value['entities'])
            && is_array($this->value['entities'])
        ) {
            $this->entities = $this->createEntities();
            $this->preparePlaceHolders();
        }

        if (isset($this->value['template']) && is_array($this->value['template'])) {
            $csvTemplate = $this->objectManager->create(
                CsvTemplate::class,
                [
                    'config' => array_merge(
                        $this->value['template'],
                        [
                            'placeholders' => $this->placeholders
                        ]
                    )
                ]
            );
            $this->data = $this->generator->generate($csvTemplate);
            $this->convertCsvToArray($csvTemplate->getCsv());

            return parent::getData($key);
        }

        $filename = MTF_TESTS_PATH . $this->value;
        if (!file_exists($filename)) {
            throw new \Exception("CSV file '{$filename}'' not found on the server.");
        }

        $this->data = $filename;

        return parent::getData($key);
    }

    /**
     * Get entities fixtures.
     *
     * @return FixtureInterface[]
     */
    public function getEntities()
    {
        return $this->entities ?: [];
    }

    /**
     * Get fixture data.
     *
     * @return array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Create entities from configuration of variation.
     *
     * @return FixtureInterface[]
     */
    private function createEntities()
    {
        $entities = [];
        foreach ($this->value['entities'] as $key => $entityDataSet) {
            list($fixtureCode, $dataset) = explode('::', $entityDataSet);

            /** @var FixtureInterface[] $entities */
            $entities[$key] = $this->fixtureFactory->createByCode(trim($fixtureCode), ['dataset' => trim($dataset)]);
            if ($entities[$key]->hasData('id') === false) {
                $entities[$key]->persist();
            }
        }
        ksort($entities);
        return $entities;
    }

    /**
     * Create placeholders for entities.
     *
     * @return void
     */
    private function preparePlaceHolders()
    {
        $placeholders = [];
        $key = 0;
        foreach ($this->entities as $entity) {
            $entityData = $this->prepareEntityData($entity);
            foreach ($this->csvTemplate['entity_' . $key] as $entityKey => $importedEntityData) {
                $values = implode('', array_values($importedEntityData));
                preg_match_all('/\%(.*)\%/U', $values, $indexes);
                foreach ($indexes[1] as $index) {
                    if (isset($entityData[$index])) {
                        $placeholders['entity_' . $key][$entityKey]["%{$index}%"] = $entityData[$index];
                    }
                    if (isset($entityData['code'])) {
                        $placeholders['entity_' . $key][$entityKey][$entityData['code']]
                            = isset($entityData[$entityData['code']])
                            ? $entityData[$entityData['code']]
                            : 'Main Website';
                    }
                }
            }
            $key++;
        }
        $this->placeholders = $placeholders;
    }

    /**
     * Prepare entity data.
     *
     * @param FixtureInterface $entity
     * @return array
     */
    public function prepareEntityData(FixtureInterface $entity)
    {
        $entityData = $entity->getData();
        if (isset($entityData['quantity_and_stock_status'])) {
            $entityData = array_merge($entityData, $entityData['quantity_and_stock_status']);
        }
        if (isset($entityData['website_ids'])) {
            $entityData = array_merge($entityData, $this->getWebsitesData($entity));
        }
        if ($entity->getDataConfig() && ('simple' !== $entity->getDataConfig()['type_id'])) {
            $class = ucfirst($entity->getDataConfig()['type_id']);
            $file = ObjectManager::getInstance()
                ->create("\\Magento\\{$class}ImportExport\\Test\\Fixture\\Import\\File");
            $entityData = $file->getData($entity, $this->fixtureFactory);
        }
        return $entityData;
    }

    /**
     * Add websites data to array.
     *
     * @param FixtureInterface $entity
     * @return array
     */
    private function getWebsitesData(FixtureInterface $entity)
    {
        $entityData = [];
        $currency = isset($this->value['template']['websiteCurrency'])
            ? "[{$this->value['template']['websiteCurrency']}]"
            : '[USD]';

        $websites = $entity->getDataFieldConfig('website_ids')['source']->getWebsites();
        foreach ($websites as $website) {
            if ($website->getCode() === 'base') {
                $currency = isset($this->value['template']['mainWebsiteCurrency'])
                    ? $this->value['template']['mainWebsiteCurrency']
                    : '[USD]';
                $this->mainWebsiteMapping['base'] = $website->getName() . "[{$currency}]";
                break;
            }
            $entityData['code'] = $website->getCode();
            $entityData[$website->getCode()] = $website->getName() . $currency;
        }
        return $entityData;
    }

    /**
     * Convert csv to array.
     *
     * @param string $csvContent
     * @return array
     */
    public function convertCsvToArray($csvContent)
    {
        foreach ($this->placeholders as $placeholderData) {
            foreach ($placeholderData as $data) {
                $csvContent = strtr($csvContent, $data);
            }
        }
        if (is_array($this->mainWebsiteMapping)) {
            $csvContent = strtr($csvContent, $this->mainWebsiteMapping);
        }
        $this->prepareCsv($csvContent);
    }

    /**
     * Prepare csv data for converting to array.
     *
     * @param string $csvContent
     * @return void
     */
    private function prepareCsv($csvContent)
    {
        $this->csv = [];
        foreach (str_getcsv($csvContent, "\n") as $value) {
            $explodedArray = explode(",", $value);
            $count = count($explodedArray);
            for ($i = 0; $i < $count; $i++) {
                if (preg_match('/^\".*[^"]$/U', $explodedArray[$i])) {
                    $compiledData = $this->compileToOneString($i, $explodedArray);
                    $i = $compiledData['index'];
                    $explodedArray = $compiledData['explodedArray'];
                } else {
                    $explodedArray[$i] = str_replace('"', '', $explodedArray[$i]);
                }
            }
            $data = array_diff($explodedArray, ['%to_delete%']);
            $this->csv[] = $data;
        }
    }

    /**
     * Compile exploded data from "quotes" to one string.
     *
     * @param int $index
     * @param array $explodedArray
     * @return string
     */
    private function compileToOneString($index, array $explodedArray)
    {
        $count = count($explodedArray);
        $implodedKey = $index;
        while ((++$index <= $count) && !preg_match('/^[^"].*\"$/U', $explodedArray[$index])) {
            $explodedArray[$implodedKey] .= ',' . $explodedArray[$index];
            $explodedArray[$index] = '%to_delete%';
        }
        $explodedArray[$implodedKey] .= ',' . $explodedArray[$index];
        $explodedArray[$index] = '%to_delete%';
        $explodedArray[$implodedKey] = str_replace('"', '', $explodedArray[$implodedKey]);
        return ['index' => $index, 'explodedArray' => $explodedArray];
    }

    /**
     * Return csv as array.
     *
     * @return array
     */
    public function getCsv()
    {
        return $this->csv;
    }
}
