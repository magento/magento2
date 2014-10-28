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

namespace Magento\Tax\Service\V1\Collection;

use Magento\Core\Model\EntityFactory;
use Magento\Framework\Service\AbstractServiceCollection;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Tax\Model\Calculation\TaxRuleConverter;
use Magento\Tax\Service\V1\TaxRuleServiceInterface;
use Magento\Tax\Service\V1\Data\TaxRule;
use Magento\Framework\Service\V1\Data\SortOrderBuilder;

/**
 * Tax rule collection for a grid backed by Services
 */
class TaxRuleCollection extends AbstractServiceCollection
{
    /**
     * @var TaxRuleServiceInterface
     */
    protected $ruleService;

    /**
     * @var TaxRuleConverter
     */
    protected $ruleConverter;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TaxRuleServiceInterface $ruleService
     * @param TaxRuleConverter $ruleConverter
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        EntityFactory $entityFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        TaxRuleServiceInterface $ruleService,
        TaxRuleConverter $ruleConverter
    ) {
        parent::__construct($entityFactory, $filterBuilder, $searchCriteriaBuilder, $sortOrderBuilder);
        $this->ruleService = $ruleService;
        $this->ruleConverter = $ruleConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $searchCriteria = $this->getSearchCriteria();
            $searchResults = $this->ruleService->searchTaxRules($searchCriteria);
            $this->_totalRecords = $searchResults->getTotalCount();
            foreach ($searchResults->getItems() as $taxRule) {
                $this->_addItem($this->createTaxRuleCollectionItem($taxRule));
            }
            $this->_setIsLoaded();
        }
        return $this;
    }

    /**
     * Creates a collection item that represents a tax rule for the tax rules grid.
     *
     * @param TaxRule $taxRule Input data for creating the item.
     * @return \Magento\Framework\Object Collection item that represents a tax rule
     */
    protected function createTaxRuleCollectionItem(TaxRule $taxRule)
    {
        $collectionItem = new \Magento\Framework\Object();
        $collectionItem->setTaxCalculationRuleId($taxRule->getId());
        $collectionItem->setCode($taxRule->getCode());
        /* should cast to string so that some optional fields won't be null on the collection grid pages */
        $collectionItem->setPriority((string)$taxRule->getPriority());
        $collectionItem->setPosition((string)$taxRule->getSortOrder());
        $collectionItem->setCalculateSubtotal($taxRule->getCalculateSubtotal() ? '1' : '0');
        $collectionItem->setCustomerTaxClasses($taxRule->getCustomerTaxClassIds());
        $collectionItem->setProductTaxClasses($taxRule->getProductTaxClassIds());
        $collectionItem->setTaxRates($taxRule->getTaxRateIds());
        return $collectionItem;
    }
}
