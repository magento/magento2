<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

/**
 * Grid filters UI component
 *
 * @api
 * @since 100.0.2
 */
class Filters extends AbstractComponent implements ObserverInterface
{
    const NAME = 'filters';

    /**
     * Filters created from columns
     *
     * @var UiComponentInterface[]
     */
    protected $columnFilters = [];

    /**
     * Maps filter declaration to type
     *
     * @var array
     */
    protected $filterMap = [
        'text' => 'filterInput',
        'textRange' => 'filterRange',
        'select' => 'filterSelect',
        'dateRange' => 'filterDate',
        'datetimeRange' => 'filterDate',
    ];

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * Filters constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param TimezoneInterface|null $localeDate
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        ?TimezoneInterface $localeDate = null
    ) {
        parent::__construct($context, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
        $this->localeDate = $localeDate ?? ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        // Set date format pattern by current locale
        $localeDateFormat = $this->localeDate->getDateFormat();
        $config['options']['dateFormat'] = $localeDateFormat;
        $this->setData('config', $config);
        parent::prepare();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function update(UiComponentInterface $component)
    {
        if ($component instanceof ColumnInterface) {
            $filterType = $component->getData('config/filter');

            if (is_array($filterType)) {
                $filterType = $filterType['filterType'];
            }

            if (!$filterType) {
                return;
            }

            if (isset($this->filterMap[$filterType]) && !isset($this->columnFilters[$component->getName()])) {
                $filterComponent = $this->uiComponentFactory->create(
                    $component->getName(),
                    $this->filterMap[$filterType],
                    ['context' => $this->getContext()]
                );
                $filterComponent->setData('config', $component->getConfiguration());
                $filterComponent->prepare();
                $this->addComponent($component->getName(), $filterComponent);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function addComponent($name, UiComponentInterface $component)
    {
        $this->columnFilters[$name] = $component;
        parent::addComponent($name, $component);
    }

    /**
     * @inheritDoc
     */
    public function getChildComponents()
    {
        $result = parent::getChildComponents();
        foreach (array_keys($this->columnFilters) as $componentName) {
            if ($this->getComponent($componentName)) {
                unset($result[$componentName]);
            }
        }

        return $result;
    }
}
