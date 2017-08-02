<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Block;

/**
 * @api
 * @since 2.0.0
 */
class Partners extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Marketplace\Model\Partners
     * @since 2.0.0
     */
    protected $partnersModel;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Marketplace::partners.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Marketplace\Model\Partners $partnersModel
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Marketplace\Model\Partners $partnersModel,
        array $data = []
    ) {
        $this->partnersModel = $partnersModel;
        parent::__construct($context, $data);
    }

    /**
     * Gets partners
     *
     * @return bool|string
     * @since 2.0.0
     */
    public function getPartners()
    {
        return $this->getPartnersModel()->getPartners();
    }

    /**
     * Gets partners model
     *
     * @return \Magento\Marketplace\Model\Partners
     * @since 2.0.0
     */
    public function getPartnersModel()
    {
        return $this->partnersModel;
    }
}
