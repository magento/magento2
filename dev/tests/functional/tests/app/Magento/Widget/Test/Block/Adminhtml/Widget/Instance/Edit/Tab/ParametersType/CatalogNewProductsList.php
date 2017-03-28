<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Filling Widget Options that have catalog new products list type.
 */
class CatalogNewProductsList extends ParametersForm
{
    /**
     * Catalog New Products List grid block.
     *
     * @var string
     */
    protected $gridBlock = './ancestor::body//*[contains(@id, "options_fieldset")]//div[contains(@class, "main-col")]';
}
