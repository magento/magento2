<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Catalog\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\GoogleOptimizer\Model\Plugin\Catalog\Category\DataProvider
 *
 * @since 2.1.0
 */
class DataProvider
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     * @since 2.1.0
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.1.0
     */
    protected $_layout;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @since 2.1.0
     */
    public function __construct(
        \Magento\GoogleOptimizer\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $result['category_view_optimization']['arguments']['data']['disabled'] =
            !$this->_helper->isGoogleExperimentActive();
        $result['category_view_optimization']['arguments']['data']['config']['componentType'] =
            \Magento\Ui\Component\Form\Fieldset::NAME;

        return $result;
    }
}
