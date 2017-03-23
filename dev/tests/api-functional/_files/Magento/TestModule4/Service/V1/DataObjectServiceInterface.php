<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\TestModule4\Service\V1;

use Magento\TestModule4\Service\V1\Entity\DataObjectRequest;
use Magento\TestModule4\Service\V1\Entity\NestedDataObjectRequest;

interface DataObjectServiceInterface
{
    /**
     * @param int $id
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectResponse
     */
    public function getData($id);

    /**
     * @param int $id
     * @param \Magento\TestModule4\Service\V1\Entity\DataObjectRequest $request
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectResponse
     */
    public function updateData($id, DataObjectRequest $request);

    /**
     * @param int $id
     * @param \Magento\TestModule4\Service\V1\Entity\NestedDataObjectRequest $request
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectResponse
     */
    public function nestedData($id, NestedDataObjectRequest $request);

    /**
     * Test return scalar value
     *
     * @param int $id
     * @return int
     */
    public function scalarResponse($id);

    /**
     * @param int $id
     * @param \Magento\TestModule4\Service\V1\Entity\ExtensibleRequestInterface $request
     * @return \Magento\TestModule4\Service\V1\Entity\DataObjectResponse
     */
    public function extensibleDataObject($id, \Magento\TestModule4\Service\V1\Entity\ExtensibleRequestInterface $request);
}
