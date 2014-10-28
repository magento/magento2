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
namespace Magento\Payment\Model\Checks;

/**
 * Class \Magento\Payment\Model\Methods\SpecificationFactory
 */
class SpecificationFactory
{
    /**
     * Composite Factory
     *
     * @var \Magento\Payment\Model\Checks\CompositeFactory
     */
    protected $compositeFactory;

    /** @var  array mapping */
    protected $mapping;

    /**
     * Construct
     *
     * @param \Magento\Payment\Model\Checks\CompositeFactory $compositeFactory
     * @param array $mapping
     */
    public function __construct(\Magento\Payment\Model\Checks\CompositeFactory $compositeFactory, array $mapping)
    {
        $this->compositeFactory = $compositeFactory;
        $this->mapping = $mapping;
    }

    /**
     * Creates new instances of payment method models
     *
     * @param array $data
     * @return Composite
     * @throws \Magento\Framework\Model\Exception
     */
    public function create($data)
    {
        $specifications = array_intersect_key($this->mapping, array_flip((array)$data));
        return $this->compositeFactory->create(array('list' => $specifications));
    }
}
