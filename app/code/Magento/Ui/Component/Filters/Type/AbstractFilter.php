<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Filters\Type;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\Component\Filters\FilterModifier;

/**
 * Abstract class AbstractFilter
 * @api
 * @since 100.0.2
 */
abstract class AbstractFilter extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'filter';

    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filters';

    /**
     * Filter data
     *
     * @var array
     */
    protected $filterData;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterModifier
     */
    protected $filterModifier;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param array $components
     * @param array $data
     * @param BookmarkManagementInterface|null $bookmarkManagement
     * @param RequestInterface|null $request
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        array $components = [],
        array $data = [],
        BookmarkManagementInterface $bookmarkManagement = null,
        RequestInterface $request = null
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $components, $data);
        $this->filterModifier = $filterModifier;

        $bookmarkManagement = $bookmarkManagement ?: ObjectManager::getInstance()
            ->get(BookmarkManagementInterface::class);
        $request = $request ?: ObjectManager::getInstance()->get(RequestInterface::class);

        $filterData = $this->getContext()->getFiltersParams();
        if (!$request->isAjax()) {
            $bookmark = $bookmarkManagement->getByIdentifierNamespace(
                'current',
                $context->getNamespace()
            );
            if (null !== $bookmark) {
                $bookmarkConfig = $bookmark->getConfig();
                $filterData = $bookmarkConfig['current']['filters']['applied'] ?? [];

                $request->setParams(
                    [
                        'paging' => $bookmarkConfig['current']['paging'] ?? []
                    ]
                );
            }
        }

        $this->filterData = $filterData;
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
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->filterModifier->applyFilterModifier($this->getContext()->getDataProvider(), $this->getName());
        parent::prepare();
    }
}
