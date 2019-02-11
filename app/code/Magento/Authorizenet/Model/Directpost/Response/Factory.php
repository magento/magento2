<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Model\Directpost\Response;

use Magento\Authorizenet\Model\Response\Factory as AuthorizenetResponseFactory;

/**
 * Factory class for @see \Magento\Authorizenet\Model\Directpost\Response
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
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
