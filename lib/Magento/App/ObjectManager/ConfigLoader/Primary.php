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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App\ObjectManager\ConfigLoader;

/**
 * Primary configuration loader for application object manager
 */
class Primary
{
    /**
     * Application mode
     *
     * @var string
     */
    protected $_appMode;

    /**
     * @var \Magento\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @param \Magento\App\Filesystem\DirectoryList $directoryList
     * @param string $appMode
     */
    public function __construct(
        \Magento\App\Filesystem\DirectoryList $directoryList,
        $appMode = \Magento\App\State::MODE_DEFAULT
    ) {
        $this->_directoryList = $directoryList;
        $this->_appMode = $appMode;
    }

    /**
     * Retrieve merged configuration from primary config files
     *
     * @return array
     */
    public function load()
    {
        $reader = new \Magento\ObjectManager\Config\Reader\Dom(
            new \Magento\App\Arguments\FileResolver\Primary(
                new \Magento\App\Filesystem(
                    $this->_directoryList,
                    new \Magento\Filesystem\Directory\ReadFactory(),
                    new \Magento\Filesystem\Directory\WriteFactory()
                ),
                new \Magento\Config\FileIteratorFactory()
            ),
            new \Magento\ObjectManager\Config\Mapper\Dom(
                new \Magento\Stdlib\BooleanUtils(),
                new \Magento\ObjectManager\Config\Mapper\ArgumentParser()
            ),
            new \Magento\ObjectManager\Config\SchemaLocator(),
            new \Magento\App\Arguments\ValidationState($this->_appMode)
        );

        return $reader->read('primary');
    }
}
