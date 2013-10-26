<?php
/**
 * Application config file resolver
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Widget\Model\Config;

class FileResolver implements \Magento\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Core\Model\Config\Modules\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\App\Dir
     */
    protected $_applicationDirs;

    /**
     * @param \Magento\Core\Model\Config\Modules\Reader $moduleReader
     * @param \Magento\App\Dir $applicationDirs
     */
    public function __construct(
        \Magento\Core\Model\Config\Modules\Reader $moduleReader,
        \Magento\App\Dir $applicationDirs
    ) {
        $this->_moduleReader = $moduleReader;
        $this->_applicationDirs = $applicationDirs;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        $fileList = array();
        switch ($scope) {
            case 'global':
                $fileList = $this->_moduleReader->getConfigurationFiles($filename);
                break;
            case 'design':
                $fileList = glob($this->_applicationDirs->getDir(\Magento\App\Dir::THEMES)
                . "/*/*/etc/$filename", GLOB_NOSORT | GLOB_BRACE);
                break;
            default:
                break;
        }
        return $fileList;
    }
}
