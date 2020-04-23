<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Shipping\Model\Rate;

/**
 * Processed rates received from a carrier.
 */
class CarrierResult extends Result
{
    /**
     * @var Result[][]
     */
    private $results = [];

    /**
     * Append result received from a carrier.
     *
     * @param Result $result
     * @param bool $appendFailed Append result's errors as well.
     * @return void
     */
    public function appendResult(Result $result, bool $appendFailed): void
    {
        $this->results[] = ['result' => $result, 'appendFailed' => $appendFailed];
    }

    /**
     * @inheritDoc
     */
    public function getAllRates()
    {
        $needsSorting = false;
        //Appending previously received results.
        while ($resultData = array_shift($this->results)) {
            if ($resultData['result']->getError()) {
                if ($resultData['appendFailed']) {
                    $this->append($resultData['result']);
                    $needsSorting = true;
                }
            } else {
                $this->append($resultData['result']);
                $needsSorting = true;
            }
        }
        if ($needsSorting) {
            parent::sortRatesByPrice();
        }

        return parent::getAllRates();
    }

    /**
     * @inheritDoc
     */
    public function getError()
    {
        $this->getAllRates();

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
