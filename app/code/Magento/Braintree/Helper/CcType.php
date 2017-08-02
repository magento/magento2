<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use Magento\Braintree\Model\Adminhtml\Source\CcType as CcTypeSource;

/**
 * Class CcType
 * @since 2.1.0
 */
class CcType
{
    /**
     * All possible credit card types
     *
     * @var array
     * @since 2.1.0
     */
    private $ccTypes = [];

    /**
     * @var \Magento\Braintree\Model\Adminhtml\Source\CcType
     * @since 2.1.0
     */
    private $ccTypeSource;

    /**
     * @param CcType $ccTypeSource
     * @since 2.1.0
     */
    public function __construct(CcTypeSource $ccTypeSource)
    {
        $this->ccTypeSource = $ccTypeSource;
    }

    /**
     * All possible credit card types
     *
     * @return array
     * @since 2.1.0
     */
    public function getCcTypes()
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }
}
