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

namespace Magento\Tax\Model\TaxClass\Source;

use Magento\Tax\Api\Data\TaxClassInterface as TaxClass;
use Magento\Tax\Api\TaxClassManagementInterface;

/**
 * Customer tax class source model.
 */
class Customer extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    protected $taxClassRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->taxClassRepository = $taxClassRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Retrieve all customer tax classes as an options array.
     *
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (!$this->_options) {
            $filter = $this->filterBuilder
                ->setField(TaxClass::KEY_TYPE)
                ->setValue(TaxClassManagementInterface::TYPE_CUSTOMER)
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilter([$filter])->create();
            $searchResults = $this->taxClassRepository->getList($searchCriteria);
            foreach ($searchResults->getItems() as $taxClass) {
                $this->_options[] = array(
                    'value' => $taxClass->getClassId(),
                    'label' => $taxClass->getClassName()
                );
            }
        }

        if ($withEmpty) {
            if (!$this->_options) {
                return array(array('value' => '0', 'label' => __('None')));
            } else {
                return array_merge(array(array('value' => '0', 'label' => __('None'))), $this->_options);
            }
        }
        return $this->_options;
    }
}
