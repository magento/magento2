<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 16.01.18
 * Time: 14:48
 */

namespace Magento\Webapi\Controller\Rest;


interface RequestProcessorInterface
{
    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return bool
     */
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request);

    /**
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return void
     * @throws \Magento\Framework\Exception\AuthorizationException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request);
}