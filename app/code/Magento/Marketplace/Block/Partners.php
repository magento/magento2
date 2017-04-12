<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Block;

class Partners extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Marketplace\Model\Partners
     */
    protected $partnersModel;

    /**
     * @var string
     */
    protected $_template = 'Magento_Marketplace::partners.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Marketplace\Model\Partners $partnersModel
     * @param array $data
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
