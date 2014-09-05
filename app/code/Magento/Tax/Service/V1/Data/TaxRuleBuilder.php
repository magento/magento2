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
namespace Magento\Tax\Service\V1\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;
use Magento\Framework\Service\Data\ObjectFactory;

/**
 * Builder for the TaxRule Service Data Object
 *
 * @method \Magento\Tax\Service\V1\Data\TaxRule create()
 */
class TaxRuleBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * TaxRate builder
     *
     * @var TaxRateBuilder
     */
    protected $taxRateBuilder;

    /**
     * Initialize dependencies.
     *
     * @param ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param TaxRateBuilder $taxRateBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        TaxRateBuilder $taxRateBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->taxRateBuilder = $taxRateBuilder;
    }
    /**
     * Set id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->_set(TaxRule::ID, $id);
    }

    /**
     * Set code
     *
     * @param String $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->_set(TaxRule::CODE, $code);
    }

    /**
     * Set customer tax class ids
     *
     * @param int[] $customerTaxClassIds
     * @return $this
     */
    public function setCustomerTaxClassIds($customerTaxClassIds)
    {
        return $this->_set(TaxRule::CUSTOMER_TAX_CLASS_IDS, $customerTaxClassIds);
    }

    /**
     * Set product tax class ids
     *
     * @param int[] $productTaxClassIds
     * @return $this
     */
    public function setProductTaxClassIds($productTaxClassIds)
    {
        return $this->_set(TaxRule::PRODUCT_TAX_CLASS_IDS, $productTaxClassIds);
    }

    /**
     * Set product tax class ids
     *
     * @param int[] $taxRateIds
     * @return $this
     */
    public function setTaxRateIds($taxRateIds)
    {
        return $this->_set(TaxRule::TAX_RATE_IDS, $taxRateIds);
    }

    /**
     * Set priority
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        return $this->_set(TaxRule::PRIORITY, (int)$priority);
    }

    /**
     * Set sort order.
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->_set(TaxRule::SORT_ORDER, (int)$sortOrder);
    }

    /**
     * Set calculate subtotal.
     *
     * @param bool $calculateSubtotal
     * @return $this
     */
    public function setCalculateSubtotal($calculateSubtotal)
    {
        return $this->_set(TaxRule::CALCULATE_SUBTOTAL, (bool)$calculateSubtotal);
    }
}
