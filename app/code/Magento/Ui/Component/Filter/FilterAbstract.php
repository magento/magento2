<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\Component\AbstractView;
use Magento\Ui\Component\Filter\FilterPool as FilterPoolProvider;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\DataProvider\Manager;

/**
 * Class Filter
 */
abstract class FilterAbstract extends AbstractView implements FilterInterface
{
    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filter';

    /**
     * Filters pool
     *
     * @var FilterPoolProvider
     */
    protected $filterPool;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param FilterPoolProvider $filterPool
     * @param DataProviderFactory $dataProviderFactory
     * @param Manager $dataProviderManager
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        DataProviderFactory $dataProviderFactory,
        Manager $dataProviderManager,
        FilterPoolProvider $filterPool,
        array $data = []
    ) {
        $this->filterPool = $filterPool;
        parent::__construct(
            $context,
            $renderContext,
            $contentTypeFactory,
            $configFactory,
            $configBuilder,
            $dataProviderFactory,
            $dataProviderManager,
            $data
        );
    }

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        $configData = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $configData = array_merge($configData, $this->getData('config'));
        }

        $this->prepareConfiguration($configData);
    }

    /**
     * Get condition by data type
     *
     * @param string|array $value
     * @return array|null
     */
    public function getCondition($value)
    {
        return $value;
    }
}
