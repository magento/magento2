<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Get button style options
 */
class ButtonStyles
{
    /**
     * Button color source getter
     *
     * @return array
     */
    public function getColor(): array
    {
        return [
            'gold' => __('Gold'),
            'blue' => __('Blue'),
            'silver' => __('Silver'),
            'black' => __('Black')
        ];
    }

    /**
     * Button layout source getter
     *
     * @return array
     */
    public function getLayout(): array
    {
        return [
            'vertical' => __('Vertical'),
            'horizontal' => __('Horizontal')
        ];
    }

    /**
     * Button shape source getter
     *
     * @return array
     */
    public function getShape(): array
    {
        return [
            'pill' => __('Pill'),
            'rect' => __('Rectangle')
        ];
    }

    /**
     * Button label source getter
     *
     * @return array
     */
    public function getLabel(): array
    {
        return [
            'checkout' => __('Checkout'),
            'pay' => __('Pay'),
            'buynow' => __('Buy Now'),
            'paypal' => __('PayPal'),
            'installment' => __('Installment'),
        ];
    }

    /**
     * Brazil button installment period source getter
     *
     * @return array
     */
    public function getBrInstallmentPeriod(): array
    {
        $numbers = range(2, 12);

        return array_combine($numbers, $numbers);
    }

    /**
     * Mexico button installment period source getter
     *
     * @return array
     */
    public function getMxInstallmentPeriod(): array
    {
        $numbers = range(3, 12, 3);

        return array_combine($numbers, $numbers);
    }
}
