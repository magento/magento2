<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Filters\Type;

use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\View\Element\BookmarkContextInterface;
use Magento\Ui\View\Element\BookmarkContextProviderInterface;

/**
 * Abstract class AbstractFilter
 * @api phpcs:ignore Magento2.Classes.AbstractApi.AbstractApi -- Legacy declaration for abstract class
 * @since 100.0.2
 * phpcs:disable Magento2.Classes.AbstractApi
 */
abstract class AbstractFilter extends AbstractComponent
{
    /**
     * Component name
     */
    public const NAME = 'filter';

    /**
     * Filter variable name
     *
     * @deprecated Use ContextInterface for retrieve filters
     * @see ContextInterface
     */
    public const FILTER_VAR = 'filters';

    /**
     * @var array
     */
    protected array $filterData;

    /**
     * @var UiComponentFactory
     */
    protected UiComponentFactory $uiComponentFactory;

    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var FilterModifier
     */
    protected FilterModifier $filterModifier;

    /**
     * AbstractFilter constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param array $components
     * @param array $data
     * @param BookmarkContextProviderInterface|null $bookmarkContextProvider
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        array $components = [],
        array $data = [],
        BookmarkContextProviderInterface $bookmarkContextProvider = null
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $components, $data);
        $this->filterModifier = $filterModifier;

        $bookmarkContextProvider = $bookmarkContextProvider ?: ObjectManager::getInstance()
            ->get(BookmarkContextProviderInterface::class);
        $bookmarkContext = $bookmarkContextProvider->getByUiContext($context);
        $this->filterData = $bookmarkContext->getFilterData();
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
     * Prepare filter component
     *
     * @inheridoc
     */
    public function prepare()
    {
        $this->filterModifier->applyFilterModifier($this->getContext()->getDataProvider(), $this->getName());
        parent::prepare();
    }
}
