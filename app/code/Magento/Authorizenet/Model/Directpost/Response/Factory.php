<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost\Response;

use Magento\Authorizenet\Model\Response\Factory as AuthorizenetResponseFactory;

/**
 * Factory class for @see \Magento\Authorizenet\Model\Directpost\Response
 */
class Factory extends AuthorizenetResponseFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Authorizenet\Model\Directpost\Response::class
    ) {
        parent::__construct($objectManager, $instanceName);
    }
}
