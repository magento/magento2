<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Block\Rma\Adminhtml\Rma\Edit\Tab\General\Shipping\Packaging;

use Magento\Framework\App\RequestInterface;
use Magento\Usps\Helper\Data as UspsHelper;
use Magento\Usps\Model\Carrier;

/**
 * Rma block plugin
 */
class Plugin
{
    /**
     * Usps helper
     *
     * @var \Magento\Usps\Helper\Data
     */
    protected $uspsHelper;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Construct
     *
     * @param \Magento\Usps\Helper\Data $uspsHelper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(UspsHelper $uspsHelper, RequestInterface $request)
    {
        $this->uspsHelper = $uspsHelper;
        $this->request = $request;
    }

    /**
     * Add rule to isGirthAllowed() method
     *
     * @param \Magento\Framework\Object $subject $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsGirthAllowed(\Magento\Framework\Object $subject, $result)
    {
        return $result && $this->uspsHelper->displayGirthValue($this->request->getParam('method'));
    }

    /**
     * Add rule to isGirthAllowed() method
     *
     * @param \Magento\Framework\Object $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundCheckSizeAndGirthParameter(\Magento\Framework\Object $subject, \Closure $proceed)
    {
        $carrier = $subject->getCarrier();
        $size = $subject->getSourceSizeModel();

        $girthEnabled = false;
        $sizeEnabled = false;
        if ($carrier && isset($size[0]['value'])) {
            if ($size[0]['value'] == Carrier::SIZE_LARGE && in_array(
                key($subject->getContainers()),
                [Carrier::CONTAINER_NONRECTANGULAR, Carrier::CONTAINER_VARIABLE]
            )
            ) {
                $girthEnabled = true;
            }

            if (in_array(
                key($subject->getContainers()),
                [Carrier::CONTAINER_NONRECTANGULAR, Carrier::CONTAINER_RECTANGULAR, Carrier::CONTAINER_VARIABLE]
            )
            ) {
                $sizeEnabled = true;
            }
        }

        return [$girthEnabled, $sizeEnabled];
    }
}
