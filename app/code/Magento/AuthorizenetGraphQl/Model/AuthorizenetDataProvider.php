<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model;

use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class AuthorizenetDataProvider
 *
 * @package Magento\AuthorizenetGraphQl\Model
 */
class AuthorizenetDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'input/payment_method/additional_data/authorizenet';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * AuthorizenetDataProvider constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Returns additional data
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        return $this->arrayManager->get(static::PATH_ADDITIONAL_DATA, $args) ?? [];
    }
}
