<?php
/**
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
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Connect\Model\Extension;

/**
 * Extension packages files collection
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Data\Collection\Filesystem
{
    /**
     * Files and folders regexsp
     *
     * @var string
     */
    protected $_allowedDirsMask = '/^[a-z0-9\.\-]+$/i';

    /**
     * @var string
     */
    protected $_allowedFilesMask = '/^[a-z0-9\.\-\_]+\.(xml|ser)$/i';

    /**
     * @var string
     */
    protected $_disallowedFilesMask = '/^package\.xml$/i';

    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Filesystem\Directory\Write
     */
    protected $connectDirectory;

    /**
     * Set base dir
     *
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(\Magento\Core\Model\EntityFactory $entityFactory, \Magento\App\Filesystem $filesystem)
    {
        parent::__construct($entityFactory);
        $this->filesystem = $filesystem;
        $this->connectDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);
        $this->connectDirectory->create('connect');
        $this->addTargetDir($this->connectDirectory->getAbsolutePath('connect'));
    }

    /**
     * Row generator
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        $row['package'] = preg_replace(
            '/\.(xml|ser)$/',
            '',
            str_replace($this->connectDirectory->getAbsolutePath('connect/'), '', $filename)
        );
        $row['filename_id'] = $row['package'];
        $folder = explode('/', $row['package']);
        array_pop($folder);
        $row['folder'] = '/';
        if (!empty($folder)) {
            $row['folder'] = implode('/', $folder) . '/';
        }
        return $row;
    }

    /**
     * Get all folders as options array
     *
     * @return array
     */
    public function collectFolders()
    {
        $collectFiles = $this->_collectFiles;
        $collectDirs = $this->_collectDirs;
        $this->setCollectFiles(false)->setCollectDirs(true);

        $this->_collectRecursive($this->connectDirectory->getAbsolutePath('connect'));
        $result = array('/' => '/');
        foreach ($this->_collectedDirs as $dir) {
            $dir = substr($this->connectDirectory->getRelativePath($dir), strlen('connect/')) . '/';
            $result[$dir] = $dir;
        }

        $this->setCollectFiles($collectFiles)->setCollectDirs($collectDirs);
        return $result;
    }
}
