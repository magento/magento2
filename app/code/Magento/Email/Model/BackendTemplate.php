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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Email\Model;

/**
 * Adminhtml email template model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendTemplate extends Template
{
    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Magento\Backend\Model\Config\Structure
     */
    private $_structure;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Registry $registry
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param \Magento\Email\Model\Template\FilterFactory $emailFilterFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Backend\Model\Config\Structure $structure
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\View\DesignInterface $design,
        \Magento\Registry $registry,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\App\Filesystem $filesystem,
        \Magento\View\Url $viewUrl,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\ConfigInterface $coreConfig,
        \Magento\Email\Model\Template\FilterFactory $emailFilterFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Backend\Model\Config\Structure $structure,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $filesystem,
            $viewUrl,
            $viewFileSystem,
            $coreStoreConfig,
            $coreConfig,
            $emailFilterFactory,
            $emailConfig,
            $data
        );
        $this->_structure = $structure;
    }

    /**
     * Collect all system config paths where current template is used as default
     *
     * @return array
     */
    public function getSystemConfigPathsWhereUsedAsDefault()
    {
        $templateCode = $this->getOrigTemplateCode();
        if (!$templateCode) {
            return array();
        }

        $configData = $this->_coreConfig->getValue(null, 'default');
        $paths = $this->_findEmailTemplateUsages($templateCode, $configData, '');
        return $paths;
    }

    /**
     * Find nodes which are using $templateCode value
     *
     * @param string $code
     * @param array $data
     * @param string $path
     * @return array
     */
    protected function _findEmailTemplateUsages($code, array $data, $path)
    {
        $output = array();
        foreach ($data as $key => $value) {
            $configPath = $path ? $path . '/' . $key : $key;
            if (is_array($value)) {
                $output = array_merge(
                    $output,
                    $this->_findEmailTemplateUsages($code, $value, $configPath)
                );
            } else {
                if ($value == $code) {
                    $output[] = array('path' => $configPath);
                }
            }
        }
        return $output;
    }

    /**
     * Collect all system config paths where current template is currently used
     *
     * @return array
     */
    public function getSystemConfigPathsWhereUsedCurrently()
    {
        $templateId = $this->getId();
        if (!$templateId) {
            return array();
        }

        $templatePaths = $this->_structure
            ->getFieldPathsByAttribute('source_model', 'Magento\Backend\Model\Config\Source\Email\Template');

        if (!count($templatePaths)) {
            return array();
        }

        $configData = $this->_getResource()->getSystemConfigByPathsAndTemplateId($templatePaths, $templateId);
        if (!$configData) {
            return array();
        }

        return $configData;
    }
}
