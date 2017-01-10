<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report\ConditionAppliers;

/**
 * Class AppliersPool
 */
class AppliersPool
{
    /** @var ApplierInterface[] */
    private $appliersPool = [];

    /**
     * AppliersPool constructor.
     * @param ApplierInterface[] $appliers
     */
    public function __construct(array $appliers)
    {
        $this->appliersPool = $appliers;
        $this->checkAppliers();
    }

    /**
     * Check appliers's types
     *
     * @return bool
     */
    private function checkAppliers()
    {
        foreach ($this->appliersPool as $applier) {
            if (!($applier instanceof ApplierInterface)) {
                throw new \InvalidArgumentException('Report filter applier must implement ApplierInterface');
            }
        }
        return true;
    }

    /**
     * Get condition applier for filter
     * @param object $filter
     * @return null|ApplierInterface
     */
    public function getApplier($filter)
    {
        if (is_object($filter)) {
            $filterClass = get_class($filter);
            if (array_key_exists($filterClass, $this->appliersPool)) {
                return $this->appliersPool[$filterClass];
            }
        }
        return null;
    }
}
