<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model\Rate;

use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Carriers rates for different packages.
 */
class PackageResult extends Result
{
    /**
     * @var array
     */
    private $packageResults = [];

    /**
     * @var ErrorFactory
     */
    private $errorFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ErrorFactory $errorFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ErrorFactory $errorFactory
    ) {
        parent::__construct($storeManager);
        $this->errorFactory = $errorFactory;
    }

    /**
     * Add result for a number of packages.
     *
     * @param Result $result
     * @param int $numberOfPackages
     * @return void
     */
    public function appendPackageResult(Result $result, int $numberOfPackages): void
    {
        $this->packageResults[] = ['packages' => $numberOfPackages, 'result' => $result];
    }

    /**
     * @inheritDoc
     */
    public function getAllRates()
    {
        //Process results for packages
        while ($resultData = array_shift($this->packageResults)) {
            /** @var Result $result */
            $result = $resultData['result'];
            $result->updateRatePrice($resultData['packages']);

            foreach ($result->getAllRates() as $currentRate) {
                foreach ($this->_rates as $rate) {
                    if ($rate->getMethod() === $currentRate->getMethod()) {
                        if ($rate === $currentRate) {
                            throw new \InvalidArgumentException('Same object received from carrier.');
                        }
                        $rate->setPrice($rate->getPrice() + $currentRate->getPrice());
                        continue 2;
                    }
                }
                //Rate does not exist
                $this->append($currentRate);
            }
        }

        return parent::getAllRates();
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        if (!$this->_rates && !$this->packageResults) {
            $this->setError(true);
            $this->_rates[] = $this->errorFactory->create();
        } else {
            $this->getAllRates();
        }

        return parent::getError();
    }

    /**
     * @inheritDoc
     */
    public function getRateById($id)
    {
        $this->getAllRates();

        return parent::getRateById($id);
    }

    /**
     * @inheritDoc
     */
    public function getCheapestRate()
    {
        $this->getAllRates();

        return parent::getCheapestRate();
    }

    /**
     * @inheritDoc
     */
    public function getRatesByCarrier($carrier)
    {
        $this->getAllRates();

        return parent::getRatesByCarrier($carrier);
    }

    /**
     * @inheritDoc
     */
    public function asArray()
    {
        $this->getAllRates();

        return parent::asArray();
    }

    /**
     * @inheritDoc
     */
    public function sortRatesByPrice()
    {
        $this->getAllRates();

        return parent::sortRatesByPrice();
    }

    /**
     * @inheritDoc
     */
    public function updateRatePrice($packageCount)
    {
        $this->getAllRates();

        return parent::updateRatePrice($packageCount);
    }
}
