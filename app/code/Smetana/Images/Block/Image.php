<?php
namespace Smetana\Images\Block;

use Magento\Framework\Filesystem;

class Image extends \Magento\Framework\View\Element\Template
{
    private $helper;

    public function __construct(
        \Smetana\Images\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function getImage()
    {
        //$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$helper = $objectManager->create('Smetana\Images\Helper\Data');
        $name = $this->helper->getConfig('smetana_section/smetana_group/smetana_upload_image');
       // $path = $this->mediaDirectory->getAbsolutePath($this->_appendScopeInfo('products_image')) . '/' . $name;
        return $path;
    }
}