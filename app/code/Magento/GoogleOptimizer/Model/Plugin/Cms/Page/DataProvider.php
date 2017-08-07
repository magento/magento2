<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Cms\Page;

/**
 * Class \Magento\GoogleOptimizer\Model\Plugin\Cms\Page\DataProvider
 *
 * @since 2.1.0
 */
class DataProvider
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     * @since 2.1.0
     */
    private $helper;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @since 2.1.0
     */
    public function __construct(
        \Magento\GoogleOptimizer\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Cms\Model\Page\DataProvider $subject
     * @param array $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function afterPrepareMeta(\Magento\Cms\Model\Page\DataProvider $subject, $result)
    {
        $result['page_view_optimization']['arguments']['data']['disabled'] = !$this->helper->isGoogleExperimentActive();
        $result['page_view_optimization']['arguments']['data']['config']['componentType'] =
            \Magento\Ui\Component\Form\Fieldset::NAME;
        $result['page_view_optimization']['arguments']['data']['config']['label'] = __('Page View Optimization');
        return $result;
    }
}
