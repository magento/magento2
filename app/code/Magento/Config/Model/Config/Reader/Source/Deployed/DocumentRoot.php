<?php
/**
 * @by SwiftOtter, Inc., 04/01/2017
 * @website https://swiftotter.com
 **/

namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\Filesystem\DirectoryList;

class DocumentRoot
{
    private $config;

    public function __construct(\Magento\Framework\App\DeploymentConfig $config)
    {
        $this->config = $config;
    }

    public function getPath()
    {
        return $this->isPub() ? DirectoryList::PUB : DirectoryList::ROOT;
    }

    public function isPub()
    {
        return (bool)$this->config->get(ConfigOptionsListConstants::CONFIG_PATH_DOCUMENT_ROOT_IS_PUB);
    }
}