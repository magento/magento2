<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Catalog\Product\Category;

use \Magento\Catalog\Ui\DataProvider\Product\Form\NewCategoryDataProvider;

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
     * @param NewCategoryDataProvider $subject
     * @param array $result
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(NewCategoryDataProvider $subject, $result)
    {
        $isDisabled = !$this->helper->isGoogleExperimentActive();
        $experimentScriptFieldConfig = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentDisabled' => $isDisabled
                    ]
                ]
            ]
        ];

        $codeIdFieldConfig = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentDisabled' => $isDisabled
                    ]
                ]
            ]
        ];
        $result['data']['children']['experiment_script'] = $experimentScriptFieldConfig;
        $result['data']['children']['code_id'] = $codeIdFieldConfig;

        return $result;
    }
}
