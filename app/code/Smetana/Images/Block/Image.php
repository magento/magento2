<?php
namespace Smetana\Images\Block;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Image extends \Magento\Framework\View\Element\Template
{
    public $scopeConfig;
    public $helper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Smetana\Images\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getConfig($option)
    {
        return $this->scopeConfig->getValue(
            "smetana_section/smetana_group/$option",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getImage()
    {
        $image = $this->getConfig('smetana_upload_image');
        if ($image === null) {
            return false;
        }
        $path = $this->helper->resize(
            $image,
            $this->getConfig('image_width'),
            $this->getConfig('image_height')
        );
        return $path == false ? '' : substr($path, strpos($path, 'pub'));
    }
}