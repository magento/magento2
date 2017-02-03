<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Widget;

use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Is used to represent any form with tabs on the page.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTabs extends Form
{
    /**
     * Tabs list.
     *
     * @var array
     */
    protected $tabs = [];

    /**
     * Fields which aren't assigned to any tab.
     *
     * @var array
     */
    protected $unassignedFields = [];

    /**
     * Page header selector.
     *
     * @var string
     */
    protected $header = 'header';

    /**
     * @constructor
     * @param SimpleElement $element
     * @param Mapper $mapper
     * @param BlockFactory $blockFactory
     * @param BrowserInterface $browser
     * @param array $config
     */
    public function __construct(
        SimpleElement $element,
        Mapper $mapper,
        BlockFactory $blockFactory,
        BrowserInterface $browser,
        array $config = []
    ) {
        parent::__construct($element, $blockFactory, $mapper, $browser, $config);
    }

    /**
     * Initialize block.
     */
    protected function init()
    {
        $this->tabs = $this->getFormMapping();
    }

    /**
     * Fill form with tabs.
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
     * Fill specified form with tabs.
     *
     * @param array $tabs
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    protected function fillTabs(array $tabs, SimpleElement $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        foreach ($tabs as $tabName => $tabFields) {
            $tab = $this->getTab($tabName);
            $this->openTab($tabName);
            $tab->fillFormTab($tabFields, $context);
        }
        if (!empty($this->unassignedFields)) {
            $this->fillMissedFields();
        }

        return $this;
    }

    /**
     * Fill fields which weren't found on filled tabs.
     *
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function fillMissedFields()
    {
        foreach ($this->tabs as $tabName => $tabData) {
            $tab = $this->getTab($tabName);
            if ($this->openTab($tabName) && $this->isTabVisible($tabName)) {
                $mapping = $tab->dataMapping($this->unassignedFields);
                foreach ($mapping as $fieldName => $data) {
                    $element = $tab->_rootElement->find($data['selector'], $data['strategy'], $data['input']);
                    if ($element->isVisible()) {
                        $element->setValue($data['value']);
                        unset($this->unassignedFields[$fieldName]);
                    }
                }
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
     * Get data of the tabs.
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
                $tabData = $this->getTab($tabName)->getDataFormTab();
                $data = array_merge($data, $tabData);
            }
        } else {
            $isHasData = ($fixture instanceof InjectableFixture) ? $fixture->hasData() : true;
            $tabsFields = $isHasData ? $this->getFieldsByTabs($fixture) : [];
            foreach ($tabsFields as $tabName => $fields) {
                $this->openTab($tabName);
                $tabData = $this->getTab($tabName)->getDataFormTab($fields, $this->_rootElement);
                $data = array_merge($data, $tabData);
            }
        }

        return $data;
    }

    /**
     * Update form with tabs.
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
     * Create data array for filling tabs.
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
     * Create data array for filling tabs (new fixture specification).
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
     * Create data array for filling tabs (deprecated fixture specification).
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
     * Get tab class.
     *
     * @param string $tabName
     * @return Tab
     * @throws \Exception
     */
    public function getTab($tabName)
    {
        $tabClass = $this->tabs[$tabName]['class'];
        /** @var Tab $tab */
        $tab = $this->blockFactory->create($tabClass, ['element' => $this->_rootElement]);
        if (!$tab instanceof Tab) {
            throw new \Exception('Wrong Tab Class.');
        }
        $tab->setWrapper(isset($this->tabs[$tabName]['wrapper']) ? $this->tabs[$tabName]['wrapper'] : '');
        $tab->setMapping(isset($this->tabs[$tabName]['fields']) ? (array)$this->tabs[$tabName]['fields'] : []);

        return $tab;
    }

    /**
     * Get tab element.
     *
     * @param string $tabName
     * @return ElementInterface
     */
    protected function getTabElement($tabName)
    {
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        return $this->_rootElement->find($selector, $strategy);
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return FormTabs
     */
    public function openTab($tabName)
    {
        $this->browser->find($this->header)->hover();
        $this->getTabElement($tabName)->click();
        return $this;
    }

    /**
     * Check whether tab is visible.
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        return $this->getTabElement($tabName)->isVisible();
    }
}
