<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config translate inline fields backend model
 */
namespace Magento\Config\Model\Config\Backend;

class Translate extends \Magento\Framework\App\Config\Value
{
    /**
     * Path to config node with list of caches
     *
     * @var string
     */
    const XML_PATH_INVALID_CACHES = 'dev/translate_inline/invalid_caches';

    /**
     * Set status 'invalidate' for blocks and other output caches
     *
     * @return $this
     */
    public function afterSave()
    {
        $types = array_keys(
            $this->_config->getValue(
                self::XML_PATH_INVALID_CACHES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        if ($this->isValueChanged()) {
            $this->cacheTypeList->invalidate($types);
        }

        return parent::afterSave();
    }
}
