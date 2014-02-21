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
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Helper;

/**
 * ImportExport data helper
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Core\Helper\Data
{
    /**#@+
     * XML path for config data
     */
    const XML_PATH_EXPORT_LOCAL_VALID_PATH = 'general/file/importexport_local_valid_paths';
    const XML_PATH_BUNCH_SIZE = 'general/file/bunch_size';
    /**#@-*/

    /**
     * @var \Magento\File\Size
     */
    protected $_fileSize;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\App\State $appState
     * @param \Magento\File\Size $fileSize
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\App\State $appState,
        \Magento\File\Size $fileSize,
        $dbCompatibleMode = true
    ) {
        $this->_fileSize = $fileSize;
        parent::__construct(
            $context,
            $coreStoreConfig,
            $storeManager,
            $locale,
            $appState,
            $dbCompatibleMode
        );
    }

    /**
     * Get maximum upload size message
     *
     * @return string
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->_fileSize->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = __('The total size of the uploadable files can\'t be more that %1M', $maxImageSize);
        } else {
            $message = __('System doesn\'t allow to get file upload settings');
        }
        return $message;
    }

    /**
     * Get valid path masks to files for importing/exporting
     *
     * @return string[]
     */
    public function getLocalValidPaths()
    {
        $paths = $this->_coreStoreConfig->getConfig(self::XML_PATH_EXPORT_LOCAL_VALID_PATH);
        return $paths;
    }

    /**
     * Retrieve size of bunch (how much products should be involved in one import iteration)
     *
     * @return int
     */
    public function getBunchSize()
    {
        return (int)$this->_coreStoreConfig->getConfig(self::XML_PATH_BUNCH_SIZE);
    }
}
