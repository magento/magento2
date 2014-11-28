<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\Component\Filter;

use Magento\Ui\DataProvider\Manager;
use Magento\Ui\Component\AbstractView;
use Magento\Framework\View\Element\Template;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\Component\Filter\FilterPool as FilterPoolProvider;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

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
