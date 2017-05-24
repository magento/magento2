<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

/**
 * Filling Widget Options that have cms page link type.
 */
class CmsPageLink extends ParametersForm
{
    /**
     * Cms Page Link grid block.
     *
     * @var string
     */
    protected $gridBlock = './ancestor::body//*[contains(@id, "responseCntoptions_fieldset")]';

    /**
     * Path to grid.
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $pathToGrid = \Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CmsPageLink\Grid::class;
    // @codingStandardsIgnoreEnd
}
