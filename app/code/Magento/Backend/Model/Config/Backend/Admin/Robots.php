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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config backend model for robots.txt
 */
namespace Magento\Backend\Model\Config\Backend\Admin;

class Robots extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dir,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $config,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_filesystem = $filesystem;
        $this->_filePath = $dir->getDir(\Magento\App\Dir::ROOT) . '/robots.txt';
    }

    /**
     * Return content of default robot.txt
     *
     * @return bool|string
     */
    protected function _getDefaultValue()
    {
        $file = $this->_filePath;
        if ($this->_filesystem->isFile($file)) {
            return $this->_filesystem->read($file);
        }
        return false;
    }

    /**
     * Load default content from robots.txt if customer does not define own
     *
     * @return \Magento\Backend\Model\Config\Backend\Admin\Robots
     */
    protected function _afterLoad()
    {
        if (!(string) $this->getValue()) {
            $this->setValue($this->_getDefaultValue());
        }

        return parent::_afterLoad();
    }

    /**
     * Check and process robots file
     *
     * @return \Magento\Backend\Model\Config\Backend\Admin\Robots
     */
    protected function _afterSave()
    {
        if ($this->getValue()) {
            $this->_filesystem->write($this->_filePath, $this->getValue());
        }

        return parent::_afterSave();
    }
}
