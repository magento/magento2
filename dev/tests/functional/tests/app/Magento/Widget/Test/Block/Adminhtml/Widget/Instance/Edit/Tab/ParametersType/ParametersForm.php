<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Template;

/**
 * Responds for filling widget options form.
 */
class ParametersForm extends Form
{
    /**
     * Select entity.
     *
     * @var string
     */
    protected $selectEntity = '.btn-chooser';

    /**
     * Grid block locator.
     *
     * @var string
     */
    protected $gridBlock = '';

    /**
     * Path to grid.
     *
     * @var string
     */
    protected $pathToGrid = '';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Filling widget options form.
     *
     * @param array $parametersFields
     * @param SimpleElement $element
     * @return void
     */
    public function fillForm(array $parametersFields, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($parametersFields);
        $this->_fill(array_diff_key($mapping, ['entities' => '']), $element);
        if (isset($parametersFields['entities'])) {
            $this->selectEntity($mapping['entities']);
        }
    }

    /**
     * Getting options data form on the widget options form.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return $this
     */
    public function getDataOptions(array $fields = null, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping, $element);
    }

    /**
     * Select entity on widget options tab.
     *
     * @param array $entities
     * @return void
     */
    protected function selectEntity(array $entities)
    {
        foreach ($entities['value'] as $entity) {
            $this->_rootElement->find($this->selectEntity)->click();
            $this->getTemplateBlock()->waitLoader();
            $grid = $this->blockFactory->create(
                $this->pathToGrid,
                [
                    'element' => $this->_rootElement->find($this->gridBlock, Locator::SELECTOR_XPATH)
                ]
            );
            $grid->searchAndSelect($this->prepareFilter($entity));
        }
    }

    /**
     * Prepare filter for grid.
     *
     * @param InjectableFixture $entity
     * @return array
     */
    protected function prepareFilter(InjectableFixture $entity)
    {
        return ['title' => $entity->getTitle()];
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }
}
