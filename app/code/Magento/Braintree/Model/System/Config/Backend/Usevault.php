<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\System\Config\Backend;

class Usevault extends \Magento\Framework\App\Config\Value
{
    const BRAINTREE_ENABLED_CONFIG_PATH = 'payment/braintree/active';

    /**
     * Prepare data before save
     * If payment method is disabled, vault also have to be disabled
     *
     * @return $this
     */
    public function beforeSave()
    {
        $data = $this->getData();
        if (isset($data['groups']['braintree']['fields']['active']['value']) &&
            !$data['groups']['braintree']['fields']['active']['value']) {
            $this->setValue(0);
        }
    }
}
