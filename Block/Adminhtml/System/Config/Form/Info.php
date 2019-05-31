<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * lease visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

/**
 * Admin Login As Customer configurations information block
 */
class Info extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * Info constructor.
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param null|\Magento\Framework\App\ProductMetadataInterface
     */
    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        $metadata = null
    ) {
        parent::__construct($context, $data);
        $this->moduleList       = $moduleList;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->metadata = $metadata ?: $objectManager->get(
            \Magento\Framework\App\ProductMetadataInterface::class
        );
    }

    /**
     * Return info block html
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $m = $this->moduleList->getOne($this->getModuleName());
        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            Login As Customer Extension v' . $m['setup_version'] . ' was developed by <a href="http://magefan.com/" target="_blank">MageFan</a>.
        </div>';
        $html .= '<style>#row_mfloginascustomer_general_key{display:none}</style>';
        if ($this->metadata->getEdition() != 'Community') {
            $html .= '<script>
        require([
            "jquery",
            "domReady!"
        ], function($){
             $("#row_mfloginascustomer_general_key").show();
        });
        </script>';
        }

        return $html;
    }
}
