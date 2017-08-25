<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

/**
 * Class \Magento\Customer\Model\Context
 *
 */
class Context
{
    /**
     * Customer group cache context
     */
    const CONTEXT_GROUP = 'customer_group';

    /**
     * Customer authorization cache context
     */
    const CONTEXT_AUTH = 'customer_logged_in';
}
