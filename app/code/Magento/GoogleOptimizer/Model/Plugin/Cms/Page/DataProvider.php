<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Cms\Page;

class DataProvider
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
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
