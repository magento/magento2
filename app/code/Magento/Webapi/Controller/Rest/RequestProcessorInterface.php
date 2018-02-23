<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

/**
 *  Request processor interface
 */
interface RequestProcessorInterface
{

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return void
     * @throws \Magento\Framework\Exception\AuthorizationException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request);

    /**
     * @return string
     */
    public function getProcessorPath();
}
