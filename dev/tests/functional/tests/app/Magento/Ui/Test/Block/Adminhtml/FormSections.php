<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Is used to represent a new unified form with collapsible sections inside.
 */
class FormSections extends AbstractFormContainers
{
    /**
     * CSS locator of collapsed section.
     *
     * @var string
     */
    protected $collapsedSection = '[data-state-collapsible="closed"]';

    /**
     * CSS locator of expanded section.
     *
     * @var string
     */
    protected $expandedSection = '[data-state-collapsible="open"]';

    /**
     * Get Section class.
     *
     * @param string $sectionName
     * @return Section
     * @throws \Exception
     */
    public function getSection($sectionName)
    {
        return $this->getContainer($sectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function openContainer($sectionName)
    {
        return $this->openSection($sectionName);
    }

    /**
     * Expand section by its name
     *
     * @param string $sectionName
     * @return $this
     * @throws \Exception if section is not visible
     */
    public function openSection($sectionName)
    {
        $container = $this->getContainerElement($sectionName);
        if (!$container->isVisible()) {
            throw new \Exception('Container is not found "' . $sectionName . '""');
        }
        $section = $container->find($this->collapsedSection);
        if ($section->isVisible()) {
            $section->click();
        }

        return $this;
    }

    /**
     * Check if section is collapsible.
     *
     * @deprecated
     * @param string $sectionName
     * @return bool
     */
    public function isCollapsible($sectionName)
    {
        $section = $this->getContainerElement($sectionName);

        if ($section->find($this->collapsedSection)->isVisible()) {
            return true;
        } elseif ($section->find($this->expandedSection)->isVisible()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get require notice fields.
     *
     * @param InjectableFixture $product
     * @return array
     */
    public function getRequireNoticeFields(InjectableFixture $product)
    {
        $data = [];
        $sections = $this->getFixtureFieldsByContainers($product);
        foreach (array_keys($sections) as $sectionName) {
            $section = $this->getSection($sectionName);
            $this->openSection($sectionName);
            $errors = $section->getValidationErrors();
            if (!empty($errors)) {
                $data[$sectionName] = $errors;
            }
        }

        return $data;
    }

    /**
     * Check if section is visible.
     *
     * @deprecated
     * @param string $sectionName
     * @return bool
     */
    public function isSectionVisible($sectionName)
    {
        return !$this->getContainerElement($sectionName)->find($this->collapsedSection)->isVisible();
    }
}
