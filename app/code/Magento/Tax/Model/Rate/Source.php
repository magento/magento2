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

namespace Magento\Tax\Model\Rate;

use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\Object as Converter;
use Magento\Tax\Api\Data\TaxRateInterface as TaxRate;

/**
 * Tax rate source model.
 */
class Source implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var array */
    protected $options;

    /** @var TaxRateRepositoryInterface */
    protected $taxRateRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var Converter */
    protected $converter;

    /**
     * Initialize dependencies.
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Converter $converter
     */
    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Converter $converter
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->converter = $converter;
    }

    /**
     * Retrieve all tax rates as an options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->taxRateRepository->getList($searchCriteria);
            $this->options = $this->converter->toOptionArray(
                $searchResults->getItems(),
                TaxRate::KEY_ID,
                TaxRate::KEY_CODE
            );
        }
        return $this->options;
    }
}
