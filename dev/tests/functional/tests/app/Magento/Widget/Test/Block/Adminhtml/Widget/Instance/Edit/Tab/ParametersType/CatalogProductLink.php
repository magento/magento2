<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Filling Widget Options that have catalog product link type.
 */
class CatalogProductLink extends ParametersForm
{
    /**
     * Catalog Product Link grid block.
     *
     * @var string
     */
    protected $gridBlock = './ancestor::body//*[contains(@id, "options_fieldset")]//div[contains(@class, "main-col")]';

    /**
     * Path to grid.
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $pathToGrid = 'Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CatalogProductLink\Grid';
    // @codingStandardsIgnoreEnd

    /**
     * Prepare filter for grid.
     *
     * @param InjectableFixture $entity
     * @return array
     */
    protected function prepareFilter(InjectableFixture $entity)
    {
        return ['name' => $entity->getName()];
    }
}
