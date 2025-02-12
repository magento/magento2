<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\Input as ElementInput;

/**
 * @api
 * @since 100.0.2
 */
class Input extends AbstractFilter
{
    public const NAME = 'filter_input';

    public const COMPONENT = 'input';

    private const CONDITION_LIKE = 'like';

    /**
     * @var ElementInput
     */
    protected $wrappedComponent;

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare(): void
    {
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext()]
        );
        $this->wrappedComponent->prepare();
        // Merge JS configuration with wrapped component configuration
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );

        $this->applyFilter();

        parent::prepare();
    }

    /**
     * Apply filter
     *
     * @return void
     */
    protected function applyFilter(): void
    {
        $value = $this->filterData[$this->getName()] ?? '';
        if (strlen($value) > 0) {
            $conditionType = self::CONDITION_LIKE;
            $valueExpression = null;
            $filterConfig = $this->getData('config/filter');
            if (is_array($filterConfig)) {
                $conditionType = $filterConfig['conditionType'] ?? null;
                $valueExpression = $filterConfig['valueExpression'] ?? null;
            }
            if ($conditionType === self::CONDITION_LIKE) {
                $value = str_replace(['%', '_'], ['\%', '\_'], $value);
                $valueExpression = $valueExpression ?? '%%%s%%';
            }
            if ($valueExpression) {
                $value = sprintf($valueExpression, $value);
            }

            $filter = $this->filterBuilder->setConditionType($conditionType)
                ->setField($this->getName())
                ->setValue($value)
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
