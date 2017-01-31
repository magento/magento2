<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param \Magento\Framework\DataObject $subject $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsGirthAllowed(\Magento\Framework\DataObject $subject, $result)
    {
        return $result && $this->uspsHelper->displayGirthValue($this->request->getParam('method'));
    }

    /**
     * Add rule to isGirthAllowed() method
     *
     * @param \Magento\Framework\DataObject $subject
     * @param \Closure $proceed
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckSizeAndGirthParameter(\Magento\Framework\DataObject $subject, \Closure $proceed)
    {
        $carrier = $subject->getCarrier();
        $size = $subject->getSourceSizeModel();

        $girthEnabled = false;
        $sizeEnabled = false;
        if ($carrier && isset($size[0]['value'])) {
            if (in_array(
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
