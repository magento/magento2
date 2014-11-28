<?php
/**
 *
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

namespace Magento\Tax\Model\TaxClass;

use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Management implements \Magento\Tax\Api\TaxClassManagementInterface
{
    /**
     * Filter Builder
     *
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Search Criteria Builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Tax class repository
     *
     * @var \Magento\Tax\Model\TaxClass\Repository
     */
    protected $classRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Repository $classRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        \Magento\Tax\Model\TaxClass\Repository $classRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->classRepository = $classRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxClassId($taxClassKey, $taxClassType = self::TYPE_PRODUCT)
    {
        if (!empty($taxClassKey)) {
            switch ($taxClassKey->getType()) {
                case TaxClassKeyInterface::TYPE_ID:
                    return $taxClassKey->getValue();
                case TaxClassKeyInterface::TYPE_NAME:
                    $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                        [$this->filterBuilder->setField(TaxClassInterface::KEY_TYPE)->setValue($taxClassType)->create()]
                    )->addFilter(
                        [
                            $this->filterBuilder->setField(TaxClassInterface::KEY_NAME)
                                ->setValue($taxClassKey->getValue())
                                ->create()
                        ]
                    )->create();
                    $taxClasses = $this->classRepository->getList($searchCriteria)->getItems();
                    $taxClass = array_shift($taxClasses);
                    return (null == $taxClass) ? null : $taxClass->getClassId();
            }
        }
        return null;
    }
}
