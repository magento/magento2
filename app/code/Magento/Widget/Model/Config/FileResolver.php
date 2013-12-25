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
     * @var \Magento\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $themesDirectory;

    /**
     * @var \Magento\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $modulesDirectory;

    /**
     * @param \Magento\Filesystem                   $filesystem
     * @param \Magento\Module\Dir\Reader            $moduleReader
     * @param \Magento\Config\FileIteratorFactory   $iteratorFactory
     */
    public function __construct(
        \Magento\Filesystem                 $filesystem,
        \Magento\Module\Dir\Reader          $moduleReader,
        \Magento\Config\FileIteratorFactory $iteratorFactory
    ){
        $this->themesDirectory  = $filesystem->getDirectoryRead(\Magento\Filesystem::THEMES);
        $this->modulesDirectory = $filesystem->getDirectoryRead(\Magento\Filesystem::MODULES);
        $this->iteratorFactory  = $iteratorFactory;
        $this->_moduleReader    = $moduleReader;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->_moduleReader->getConfigurationFiles($filename);
                break;
            case 'design':
                $fileList = $this->themesDirectory->search('#/' . preg_quote($filename) . '$#');
                $iterator = $this->iteratorFactory->create($this->themesDirectory, $fileList);
                break;
            default:
                $iterator = $this->iteratorFactory->create($this->themesDirectory, array());;
                break;
        }
        return $iterator;
    }
}
