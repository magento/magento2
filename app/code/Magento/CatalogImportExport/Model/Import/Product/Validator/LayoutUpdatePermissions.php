<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;

/**
 * Validator to assert that the current user is allowed to make design updates if a layout is provided in the import
 */
class LayoutUpdatePermissions extends AbstractImportValidator
{
    private const ERROR_INSUFFICIENT_PERMISSIONS = 'insufficientPermissions';

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var array
     */
    private $allowedUserTypes = [
        UserContextInterface::USER_TYPE_ADMIN,
        UserContextInterface::USER_TYPE_INTEGRATION
    ];

    /**
     * @param UserContextInterface $userContext
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        UserContextInterface $userContext,
        AuthorizationInterface $authorization
    ) {
        $this->userContext = $userContext;
        $this->authorization = $authorization;
    }

    /**
     * Validate that the current user is allowed to make design updates
     *
     * @param array $data
     * @return boolean
     */
    public function isValid($data): bool
    {
        if (empty($data['custom_layout_update'])) {
            return true;
        }

        $userType = $this->userContext->getUserType();
        $isValid = in_array($userType, $this->allowedUserTypes)
            && $this->authorization->isAllowed('Magento_Catalog::edit_product_design');

        if (!$isValid) {
            $this->_addMessages(
                [
                    $this->context->retrieveMessageTemplate(self::ERROR_INSUFFICIENT_PERMISSIONS),
                ]
            );
        }

        return $isValid;
    }
}
