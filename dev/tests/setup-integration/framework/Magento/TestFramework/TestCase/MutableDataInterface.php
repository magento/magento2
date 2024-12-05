<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\TestCase;

/**
 * This interface allows to add data to test case dynamically, for example from startTest listeners
 * in order to reuse it later.
 */
interface MutableDataInterface
{
    /**
     * Set data providers data.
     *
     * @param  array $data
     * @return void
     */
    public function setData(int|string $dataName, array $data);

    /**
     * Retrieve data injected dynamically in test case.
     *
     * @return array
     */
    public function getData();

    /**
     * Revert data to default dataProviders data.
     *
     * @return void
     */
    public function flushData();
}
