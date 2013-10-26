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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension packages files collection
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Model\Extension;

class Collection extends \Magento\Data\Collection\Filesystem
{
    /**
     * Files and folders regexsp
     *
     * @var string
     */
    protected $_allowedDirsMask     = '/^[a-z0-9\.\-]+$/i';
    protected $_allowedFilesMask    = '/^[a-z0-9\.\-\_]+\.(xml|ser)$/i';
    protected $_disallowedFilesMask = '/^package\.xml$/i';

    /**
     * Base dir where packages are located
     *
     * @var string
     */
    protected $_baseDir = '';

    /**
     * Set base dir
     *
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     */
    public function __construct(\Magento\App\Dir $dirs, \Magento\Core\Model\EntityFactory $entityFactory)
    {
        parent::__construct($entityFactory);
        $this->_baseDir = $dirs->getDir('var') . DS . 'connect';
        $io = new \Magento\Io\File();
        $io->setAllowCreateFolders(true)->createDestinationDir($this->_baseDir);
        $this->addTargetDir($this->_baseDir);
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
        $row['package'] = preg_replace('/\.(xml|ser)$/', '', str_replace($this->_baseDir . DS, '', $filename));
        $row['filename_id'] = $row['package'];
        $folder = explode(DS, $row['package']);
        array_pop($folder);
        $row['folder'] = DS;
        if (!empty($folder)) {
            $row['folder'] = implode(DS, $folder) . DS;
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

        $this->_collectRecursive($this->_baseDir);
        $result = array(DS => DS);
        foreach ($this->_collectedDirs as $dir) {
            $dir = str_replace($this->_baseDir . DS, '', $dir) . DS;
            $result[$dir] = $dir;
        }

        $this->setCollectFiles($collectFiles)->setCollectDirs($collectDirs);
        return $result;
    }

}
