<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Widget Instance edit form.
 */
class WidgetForm extends FormTabs
{
    /**
     * Fill form with tabs.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return FormTabs
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $tabs = $this->getFixtureFieldsByContainers($fixture);
        $this->fillTabs(['settings' => $tabs['settings']], $element);
        $key = 0;
        foreach ($tabs as $key => $value) {
            if (isset($value['parameters'])) {
                break;
            }
        }
        if (isset($tabs[$key])) {
            $codeName = explode(' ', $tabs['settings']['code']['value']);
            $prepareName = [];
            foreach ($codeName as $value) {
                $prepareName[] = ucfirst(strtolower($value));
            }
            $tabs[$key]['code'] = implode(' ', $prepareName);
        }
        unset($tabs['settings']);

        return $this->fillTabs($tabs, $element);
    }
}
