<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\GoogleOptimizer\Helper\Data as DataHelper;
use Magento\GoogleOptimizer\Helper\Code as CodeHelper;

/**
 * Class GoogleOptimizer adds Product View Optimization Panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.1.0
 */
class GoogleOptimizer extends AbstractModifier
{
    const SORT_ORDER = 100;
    const GROUP_CODE = 'product-view-optimization';

    /**
     * @var LocatorInterface
     * @since 100.1.0
     */
    protected $locator;

    /**
     * @var DataHelper
     * @since 100.1.0
     */
    protected $dataHelper;

    /**
     * @var CodeHelper
     * @since 100.1.0
     */
    protected $codeHelper;

    /**
     * @param LocatorInterface $locator
     * @param DataHelper $dataHelper
     * @param CodeHelper $codeHelper
     * @since 100.1.0
     */
    public function __construct(
        LocatorInterface $locator,
        DataHelper $dataHelper,
        CodeHelper $codeHelper
    ) {
        $this->locator = $locator;
        $this->dataHelper = $dataHelper;
        $this->codeHelper = $codeHelper;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        if ($this->canShowPanel()) {
            $meta = $this->addProductViewOptimizationPanel($meta);
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        if ($this->canShowPanel()) {
            $data = $this->addDataProductViewOptimization($data);
        }

        return $data;
    }

    /**
     * Can show panel
     *
     * @return bool
     * @since 100.1.0
     */
    protected function canShowPanel()
    {
        $storeId = $this->locator->getProduct()->getStoreId();

        return $this->dataHelper->isGoogleExperimentActive($storeId);
    }

    /**
     * Add Google Experiment data for Product View Optimization panel
     *
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    protected function addDataProductViewOptimization(array $data)
    {
        $codeModel = $this->getCodeModel();

        $data[$this->locator->getProduct()->getId()]['google_experiment'] = [
            'experiment_script' => $codeModel ? $codeModel->getExperimentScript() : '',
            'code_id' => $codeModel ? $codeModel->getCodeId() : '',
        ];

        return $data;
    }

    /**
     * Get Code model
     *
     * @return \Magento\GoogleOptimizer\Model\Code|null
     * @since 100.1.0
     */
    protected function getCodeModel()
    {
        if ($this->locator->getProduct()->getId()) {
            return $this->codeHelper->getCodeObjectByEntity($this->locator->getProduct());
        }

        return null;
    }

    /**
     * Add Product View Optimization Panel
     *
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    protected function addProductViewOptimizationPanel(array $meta)
    {
        $meta[self::GROUP_CODE] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Fieldset::NAME,
                        'label' => __('Product View Optimization'),
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => $this->getNextGroupSortOrder(
                            $meta,
                            'search-engine-optimization',
                            self::SORT_ORDER
                        ),
                        'dataScope' => 'data.google_experiment',
                    ],
                ],
            ],
            'children' => [
                'experiment_script' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Textarea::NAME,
                                'dataType' => Text::NAME,
                                'label' => __('Experiment Code'),
                                'notice' => __('Experiment code should be added to the original page only.'),
                                'dataScope' => 'experiment_script',
                                'sortOrder' => 10,
                            ],
                        ],
                    ],
                ],
                'code_id' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Input::NAME,
                                'dataType' => Text::NAME,
                                'visible' => false,
                                'label' => '',
                                'dataScope' => 'code_id',
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $meta;
    }
}
