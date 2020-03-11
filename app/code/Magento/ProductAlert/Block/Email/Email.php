<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ProductAlert\Block\Email;

/**
 * Email Block used to render the form with Back In Stock product id subject to unsubscription
 *
 * @api
 */
class Email extends \Magento\ProductAlert\Block\Email\AbstractEmail
{
    /**
     * Retrieve unsubscribe url for product
     *
     * @return string
     */
    public function getProductUnsubscribeUrl(): string
    {
        return $this->getUrl('productalert/unsubscribe/stock');
    }
}
