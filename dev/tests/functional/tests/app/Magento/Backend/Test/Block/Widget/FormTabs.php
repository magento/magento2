<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Util\Iterator\File;
use Magento\Mtf\Util\XmlConverter;

/**
 * Class FormTabs
 * Is used to represent any form with tabs on the page
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTabs extends Form
{
    /**
     * @var array
     */
    protected $tabs = [];

    /**
     * @var XmlConverter
     */
    protected $xmlConverter;

    /**
     * Fields which aren't assigned to any tab
     *
     * @var array
     */
    protected $unassignedFields = [];

    /**
     * @constructor
     * @param SimpleElement $element
     * @param Mapper $mapper
     * @param BlockFactory $blockFactory
     * @param BrowserInterface $browser
     * @param XmlConverter $xmlConverter
     * @param array $config
     */
    public function __construct(
        SimpleElement $element,
        Mapper $mapper,
        BlockFactory $blockFactory,
        BrowserInterface $browser,
        XmlConverter $xmlConverter,
        array $config = []
    ) {
        $this->xmlConverter = $xmlConverter;
        parent::__construct($element, $blockFactory, $mapper, $browser, $config);
    }

    /**
     * Initialize block
     */
    protected function _init()
    {
        $this->tabs = $this->getTabs();
    }

    /**
     * Get all tabs on the form
     *
     * @return array
     */
    protected function getTabs()
    {
        $result = [];

        $paths = glob(
            MTF_TESTS_PATH . preg_replace('/Magento\/\w+/', '*/*', str_replace('\\', '/', get_class($this))) . '.xml'
        );
        $files = new File($paths);

        foreach ($files as $file) {
            $presetXml = simplexml_load_string($file);
            if ($presetXml instanceof \SimpleXMLElement) {
                $array = $this->xmlConverter->convert($presetXml);
                if (is_array($array)) {
                    $result = array_replace_recursive($result, $array);
                }
            }
        }

        return $result;
    }

    /**
     * Fill form with tabs
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        return $this->fillTabs($tabs, $element);
    }

    /**
     * Fill specified form with tabs
     *
     * @param array $tabs
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    protected function fillTabs(array $tabs, SimpleElement $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        foreach ($tabs as $tabName => $tabFields) {
            $tabElement = $this->getTabElement($tabName);
            $this->openTab($tabName);
            $tabElement->fillFormTab(array_merge($tabFields, $this->unassignedFields), $context);
            $this->updateUnassignedFields($tabElement);
        }
        if (!empty($this->unassignedFields)) {
            $this->fillMissedFields($tabs);
        }

        return $this;
    }

    /**
     * Update array with fields which aren't assigned to any tab
     *
     * @param Tab $tabElement
     */
    protected function updateUnassignedFields(Tab $tabElement)
    {
        $this->unassignedFields = array_diff_key(
            $this->unassignedFields,
            array_intersect_key($this->unassignedFields, $tabElement->setFields)
        );
    }

    /**
     * Fill fields which weren't found on filled tabs
     *
     * @param array $tabs
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function fillMissedFields(array $tabs)
    {
        foreach (array_diff_key($this->tabs, $tabs) as $tabName => $tabData) {
            $tabElement = $this->getTabElement($tabName);
            if ($this->openTab($tabName)) {
                $tabElement->fillFormTab($this->unassignedFields, $this->_rootElement);
                $this->updateUnassignedFields($tabElement);
                if (empty($this->unassignedFields)) {
                    break;
                }
            }
        }

        if (!empty($this->unassignedFields)) {
            throw new \Exception(
                'Could not find all elements on the tabs: ' . implode(', ', array_keys($this->unassignedFields))
            );
        }
    }

    /**
     * Get data of the tabs
     *
     * @param FixtureInterface|null $fixture
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(FixtureInterface $fixture = null, SimpleElement $element = null)
    {
        $data = [];

        if (null === $fixture) {
            foreach ($this->tabs as $tabName => $tab) {
                $this->openTab($tabName);
                $tabData = $this->getTabElement($tabName)->getDataFormTab();
                $data = array_merge($data, $tabData);
            }
        } else {
            $isHasData = ($fixture instanceof InjectableFixture) ? $fixture->hasData() : true;
            $tabsFields = $isHasData ? $this->getFieldsByTabs($fixture) : [];
            foreach ($tabsFields as $tabName => $fields) {
                $this->openTab($tabName);
                $tabData = $this->getTabElement($tabName)->getDataFormTab($fields, $this->_rootElement);
                $data = array_merge($data, $tabData);
            }
        }

        return $data;
    }

    /**
     * Update form with tabs
     *
     * @param FixtureInterface $fixture
     * @return FormTabs
     */
    public function update(FixtureInterface $fixture)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        foreach ($tabs as $tab => $tabFields) {
            $this->openTab($tab)->updateFormTab($tabFields, $this->_rootElement);
        }
        return $this;
    }

    /**
     * Create data array for filling tabs
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function getFieldsByTabs(FixtureInterface $fixture)
    {
        if ($fixture instanceof InjectableFixture) {
            $tabs = $this->getFixtureFieldsByTabs($fixture);
        } else {
            $tabs = $this->getFixtureFieldsByTabsDeprecated($fixture);
        }
        return $tabs;
    }

    /**
     * Create data array for filling tabs (new fixture specification)
     *
     * @param InjectableFixture $fixture
     * @return array
     */
    private function getFixtureFieldsByTabs(InjectableFixture $fixture)
    {
        $tabs = [];

        $data = $fixture->getData();
        foreach ($data as $field => $value) {
            $attributes = $fixture->getDataFieldConfig($field);
            $attributes['value'] = $value;
            if (array_key_exists('group', $attributes) && $attributes['group'] != 'null') {
                $tabs[$attributes['group']][$field] = $attributes;
            } elseif (!array_key_exists('group', $attributes)) {
                $this->unassignedFields[$field] = $attributes;
            }
        }
        return $tabs;
    }

    /**
     * Create data array for filling tabs (deprecated fixture specification)
     *
     * @param FixtureInterface $fixture
     * @return array
     * @deprecated
     */
    private function getFixtureFieldsByTabsDeprecated(FixtureInterface $fixture)
    {
        $tabs = [];

        $dataSet = $fixture->getData();
        $fields = isset($dataSet['fields']) ? $dataSet['fields'] : [];

        foreach ($fields as $field => $attributes) {
            if (array_key_exists('group', $attributes) && $attributes['group'] !== null) {
                $tabs[$attributes['group']][$field] = $attributes;
            } elseif (!array_key_exists('group', $attributes)) {
                $this->unassignedFields[$field] = $attributes;
            }
        }
        return $tabs;
    }

    /**
     * Get tab element
     *
     * @param string $tabName
     * @return Tab
     * @throws \Exception
     */
    public function getTabElement($tabName)
    {
        $tabClass = $this->tabs[$tabName]['class'];
        /** @var Tab $tabElement */
        $tabElement = $this->blockFactory->create($tabClass, ['element' => $this->_rootElement]);
        if (!$tabElement instanceof Tab) {
            throw new \Exception('Wrong Tab Class.');
        }
        $tabElement->setWrapper(isset($this->tabs[$tabName]['wrapper']) ? $this->tabs[$tabName]['wrapper'] : '');
        $tabElement->setMapping(isset($this->tabs[$tabName]['fields']) ? (array)$this->tabs[$tabName]['fields'] : []);

        return $tabElement;
    }

    /**
     * Open tab
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        $tab = $this->_rootElement->find($selector, $strategy);
        $tab->click();

        return $this;
    }
}
