<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel\Widget\Instance\Options;

use Magento\Framework\Option\ArrayInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;
use Magento\Widget\Model\Widget\Instance;

/**
 * Widget Instance Theme Id Options
 *
 * @deprecated 100.1.7 created new class that correctly loads theme options and whose name follows naming convention
 * @see \Magento\Widget\Model\ResourceModel\Widget\Instance\Options\Themes
 */
class ThemeId implements ArrayInterface
{
    /**
     * @var Instance
     */
    protected $_resourceModel;

    /**
     * @param CollectionFactory $widgetResourceModel
     */
    public function __construct(CollectionFactory $widgetResourceModel)
    {
        $this->_resourceModel = $widgetResourceModel->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_resourceModel->toOptionHash();
    }
}
