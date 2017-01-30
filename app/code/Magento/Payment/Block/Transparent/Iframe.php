<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

/**
 * Iframe block for register specific params in layout
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Iframe extends \Magento\Framework\View\Element\Template
{
    const REGISTRY_KEY = 'transparent_form_params';

    /**
     * Core registry
     *
     * @deprecated
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Payment\Model\IframeService
     */
    private $iframeService;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param \Magento\Payment\Model\IframeService|null $iframeService
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        \Magento\Payment\Model\IframeService $iframeService = null
    ) {
        $this->coreRegistry = $registry;
        $this->iframeService = $iframeService ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Payment\Model\IframeService::class);
        parent::__construct($context, $data);
    }

    /**
     * Set params once per request
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        return $this->iframeService->setParams($params);
    }

    /**
     * Return params
     *
     * @return $this
     */
    public function getParams()
    {
        return $this->iframeService->getParams();
    }
}
