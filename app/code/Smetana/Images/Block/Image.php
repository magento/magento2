<?php
namespace Smetana\Images\Block;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Image extends \Magento\Framework\View\Element\Template
{
    public $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getConfig()
    {
        return $this->scopeConfig->getValue(
            'smetana_section/smetana_group/smetana_upload_image',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

/// OLD OLD
/// use Smetana\Images\Helper\Data;
//use Magento\Framework\Filesystem;
//
//class Image extends \Magento\Framework\View\Element\Template
//{
//    public $helper;
//    public $mediaDirectory;
//
//    public function __construct(
//        Data $helper,
//        Filesystem $filesystem,
//        \Magento\Framework\View\Element\Template\Context $context
//    ) {
//        $this->helper = $helper;
//        $this->mediaDirectory = $filesystem->getDirectoryWrite('media');
//        parent::__construct($context);
//    }
//
//    public function getImage()
//    {
////        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
////        $helper = $objectManager->create('Smetana\Images\Helper\Data');
//
//
//        $name = $this->helper->getConfig('smetana_section/smetana_group/smetana_upload_image');
//        return $name;
//        $path = $this->mediaDirectory->getAbsolutePath('products_image') . '/' . $name;
//        return $path;
//    }