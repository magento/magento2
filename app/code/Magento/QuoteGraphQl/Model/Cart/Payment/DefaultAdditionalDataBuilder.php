<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

use Magento\Framework\Stdlib\ArrayManager;

class DefaultAdditionalDataBuilder implements AdditionalDataBuilderInterface
{
    private const INPUT_PATH_ADDITIONAL_DATA = 'input/payment_method/%s';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var string
     */
    private $methodCode;

    public function __construct(
        ArrayManager $arrayManager,
        string $methodCode = ''
    ) {
        $this->arrayManager = $arrayManager;
        $this->methodCode = $methodCode;
    }

    public function build(array $args): array
    {
        return $this->arrayManager->get($this->getAdditionalDataPath(), $args) ?? [];
    }

    private function getAdditionalDataPath(): string
    {
        return sprintf(static::INPUT_PATH_ADDITIONAL_DATA, $this->methodCode);
    }
}
