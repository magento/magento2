<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Adding custom parameters to GraphQL context object using the top-level user context even if it has been updated
 */
interface UserContextParametersProcessorInterface extends ContextParametersProcessorInterface
{
    /**
     * Override the dependency-injected user context
     *
     * @param UserContextInterface $userContext
     */
    public function setUserContext(UserContextInterface $userContext): void;
}
