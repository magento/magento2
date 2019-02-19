<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing\Columns;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date format column
 *
 * @api
 * @since 100.0.2
 */
class Date extends Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $timezone
     * @param BooleanUtils $booleanUtils
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        $this->booleanUtils = $booleanUtils;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $config['filter'] = [
            'filterType' => 'dateRange',
            'templates' => [
                'date' => [
                    'options' => [
                        'dateFormat' => $this->timezone->getDateFormatWithLongYear()
                    ]
                ]
            ]
        ];
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                   && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $date = $this->timezone->date(new \DateTime($item[$this->getData('name')]));
                    $timezone = isset($this->getConfiguration()['timezone'])
                        ? $this->booleanUtils->convert($this->getConfiguration()['timezone'])
                        : true;
                    if (!$timezone) {
                        $date = new \DateTime($item[$this->getData('name')]);
                    }
                    $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                }
            }
        }

        return $dataSource;
    }
}
