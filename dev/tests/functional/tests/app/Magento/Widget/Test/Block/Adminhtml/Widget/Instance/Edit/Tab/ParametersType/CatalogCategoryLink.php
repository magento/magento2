<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

use Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CatalogCategoryLink\Form;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;

/**
 * Filling Widget Options that have catalog category link type.
 */
class CatalogCategoryLink extends ParametersForm
{
    /**
     * Category Link block.
     *
     * @var string
     */
    protected $cmsCategoryLink = './ancestor::body//*[contains(@id, "responseCntoptions_fieldset")]';

    /**
     * Select category on widget options tab.
     *
     * @param array $entities
     * @return void
     */
    protected function selectEntity(array $entities)
    {
        foreach ($entities['value'] as $entity) {
            $this->_rootElement->find($this->selectEntity)->click();
            $this->getTemplateBlock()->waitLoader();
            /** @var Form $catalogCategoryLinkForm */
            $catalogCategoryLinkForm = $this->blockFactory->create(
                'Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CatalogCategoryLink\Form',
                ['element' => $this->_rootElement->find($this->cmsCategoryLink, Locator::SELECTOR_XPATH)]
            );
            $elementNew = $this->_rootElement->find($this->cmsCategoryLink, Locator::SELECTOR_XPATH);
            $entities['value'] = $entity->getPath() . '/' . $entity->getName();
            $categoryFields['entities'] = $entities;
            $catalogCategoryLinkForm->_fill($categoryFields, $elementNew);
            $this->getTemplateBlock()->waitLoader();
        }
    }
}
