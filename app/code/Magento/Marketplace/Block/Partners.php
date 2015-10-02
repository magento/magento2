<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Block;

class Partners extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Marketplace\Model\Partners
     */
    protected $partnersModel;

    protected $_template = 'Magento_Marketplace::partners.phtml';

    /**
     * @param \Magento\Marketplace\Model\Partners $partnersModel
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Magento\Marketplace\Model\Partners $partnersModel,
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->partnersModel = $partnersModel;
        parent::__construct($context);
    }

    /**
     * Gets partners
     *
     * @return bool|string
     */
    public function getPartners()
    {
        return $this->getPartnersModel()->getPartners();
    }

    /**
     * Gets partners model
     *
     * @return \Magento\Marketplace\Model\Partners
     */
    public function getPartnersModel()
    {
        return $this->partnersModel;
    }
}
