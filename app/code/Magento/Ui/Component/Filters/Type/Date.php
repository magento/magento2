<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\DataType\Date as DataTypeDate;

/**
 * Date grid filter UI Component
 *
 * @api
 * @since 100.0.2
 */
class Date extends AbstractFilter
{
    const NAME = 'filter_date';

    const COMPONENT = 'date';

    /**
     * Wrapped component
     *
     * @var DataTypeDate
     */
    protected $wrappedComponent;

    /**
     * Date format
     *
     * @var string
     * @since 100.1.2
     */
    protected static $dateFormat = 'Y-m-d H:i:s';

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
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
    protected function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];

            if (empty($value)) {
                return;
            }

            if (is_array($value)) {
                if (isset($value['from'])) {
                    $this->applyFilterByType(
                        'gteq',
                        $this->convertDatetime((string)$value['from'])
                    );
                }

                if (isset($value['to'])) {
                    $this->applyFilterByType(
                        'lteq',
                        $this->convertDatetime((string)$value['to'], 23, 59, 59)
                    );
                }
            } else {
                $this->applyFilterByType('eq', $this->convertDatetime((string)$value));
            }
        }
    }

    /**
     * Apply filter by its type
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    protected function applyFilterByType($type, $value)
    {
        if (!empty($value)) {
            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value->format(static::$dateFormat))
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $value
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return \DateTime
     */
    private function convertDatetime(string $value, int $hour = 0, int $minute = 0, int $second = 0): ?\DateTime
    {
        $value = $this->getData('config/options/showsTime')
            ? $this->wrappedComponent->convertDatetime(
                $value,
                !$this->getData('config/skipTimeZoneConversion')
            )
            : $this->wrappedComponent->convertDate(
                $value,
                $hour,
                $minute,
                $second,
                !$this->getData('config/skipTimeZoneConversion')
            );

        return $value;
    }
}
