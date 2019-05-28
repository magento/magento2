<?php

namespace Smetana\Images\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Filesystem $filesystem
    ) {

    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Smetana\Images\Helper\Data');
        $helper->getConfig('smetana_section/smetana_group/smetana_upload_image'); //default/2.jpeg


        echo 'Hello World444';
        exit();
    }
}