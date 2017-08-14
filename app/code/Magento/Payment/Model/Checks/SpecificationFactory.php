<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

/**
 * Creates complex specification.
 *
 * Use this class to register predefined list of specifications that should be added to any complex specification.
 *
 * @api
 * @since 100.0.2
 */
class SpecificationFactory
{
    /**
     * Composite Factory
     *
     * @var \Magento\Payment\Model\Checks\CompositeFactory
     */
    protected $compositeFactory;

    /**
     * @var array
     */
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($data)
    {
        $specifications = array_intersect_key($this->mapping, array_flip((array)$data));
        return $this->compositeFactory->create(['list' => $specifications]);
    }
}
