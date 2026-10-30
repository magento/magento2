<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

/**
 * Class Context
 *
 * @api
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
