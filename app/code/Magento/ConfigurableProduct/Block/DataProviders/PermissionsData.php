<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\DataProviders;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Provides permissions data into template.
 */
class PermissionsData implements ArgumentInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Constructor
     *
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Check that user is allowed to manage attributes
     *
     * @return bool
     */
    public function isAllowedToManageAttributes(): bool
    {
        return $this->authorization->isAllowed('Magento_Catalog::attributes_attributes');
    }
}
