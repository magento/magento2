<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Csp\Helper;

use Magento\Csp\Model\CspNonceProvider as CspNonceProviderModel;

/**
 * @deprecated This class was moved to Magento\Csp\Model\CspNonceProvider.
 * It is kept for backward compatibility and will be removed in a future major version.
 * @see \Magento\Csp\Model\CspNonceProvider
 */
class CspNonceProvider
{
    /**
     * @var CspNonceProviderModel
     */
    private CspNonceProviderModel $model;

    /**
     * @param CspNonceProviderModel $model
     */
    public function __construct(CspNonceProviderModel $model)
    {
        $this->model = $model;
    }

    /**
     * @deprecated
     */
    public function generateNonce(): string
    {
        return $this->model->generateNonce();
    }
}
