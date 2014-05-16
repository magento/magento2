<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @api
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Block\Mapper;
use Mtf\Fixture\FixtureInterface;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\InjectableFixture;
use Mtf\Util\Iterator\File;
use Mtf\Util\XmlConverter;

/**
 * Class FormTabs
 * Is used to represent any form with tabs on the page
 *
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
     * @param Element $element
     * @param Mapper $mapper
     * @param XmlConverter $xmlConverter
     */
    public function __construct(
        Element $element,
        Mapper $mapper,
        XmlConverter $xmlConverter
    ) {
        $this->xmlConverter = $xmlConverter;
        parent::__construct($element, $mapper);
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
     * @param Element $element
     * @return FormTabs
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);
        foreach ($tabs as $tabName => $tabFields) {
            $tabElement = $this->getTabElement($tabName);
            $this->openTab($tabName);
            $tabElement->fillFormTab(array_merge($tabFields, $this->unassignedFields), $this->_rootElement);
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
     * Verify form with tabs
     *
     * @param FixtureInterface $fixture
     * @param Element $element
     * @return bool
     */
    public function verify(FixtureInterface $fixture, Element $element = null)
    {
        $tabs = $this->getFieldsByTabs($fixture);

        foreach ($tabs as $tab => $tabFields) {
            $this->openTab($tab);
            if (!$this->getTabElement($tab)->verifyFormTab($tabFields, $this->_rootElement)) {
                return false;
            }
        }

        return true;
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
        $tabs = array();

        $data = $fixture->getData();
        foreach ($data as $field => $value) {
            $attributes = $fixture->getDataFieldConfig($field);
            $attributes['value'] = $value;
            if (array_key_exists('group', $attributes) && $attributes['group'] !== null) {
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
        $tabs = array();

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
    protected function getTabElement($tabName)
    {
        $tabClass = $this->tabs[$tabName]['class'];
        /** @var $tabElement Tab */
        $tabElement = new $tabClass($this->_rootElement, $this->mapper);
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
