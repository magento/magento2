<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Adminhtml email template model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendTemplate extends Template
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    private $_structure;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Email\Model\TemplateFactory $templateFactory
     * @param \Magento\Email\Model\Template\FilterFactory $filterFactory
     * @param \Magento\Config\Model\Config\Structure $structure
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Email\Model\Template\FilterFactory $filterFactory,
        \Magento\Config\Model\Config\Structure $structure,
        array $data = []
    ) {
        $this->_structure = $structure;
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $filterFactory,
            $data
        );
    }

    /**
     * Collect all system config paths where current template is currently used
     *
     * @return array
     */
    public function getSystemConfigPathsWhereCurrentlyUsed()
    {
        $templateId = $this->getId();
        if (!$templateId) {
            return [];
        }

        $templatePaths = $this->_structure->getFieldPathsByAttribute(
            'source_model',
            'Magento\Config\Model\Config\Source\Email\Template'
        );

        if (!count($templatePaths)) {
            return [];
        }

        $configData = $this->_getResource()->getSystemConfigByPathsAndTemplateId($templatePaths, $templateId);
        foreach ($templatePaths as $path) {
            if ($this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT) == $templateId) {
                foreach ($configData as $data) {
                    if ($data['path'] == $path) {
                        continue 2;   // don't add final fallback value if it was found in stored config
                    }
                }

                $configData[] = [
                    'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'path' => $path
                ];
            }
        }

        return $configData;
    }
}
