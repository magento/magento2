<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

/**
 * Model that finds file paths by their fileId
 */
class FileSystem
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface
     */
    protected $_resolver;

    /**
     * View service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple $resolver
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Simple $resolver,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_resolver = $resolver;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Get file name, using fallback mechanism
     *
     * @param string $filePath
     * @param string $module
     * @param array $designParams
     * @return string|false
     */
    public function getEmailTemplateFileName($filePath, $module, $designParams)
    {
        $this->_assetRepo->updateDesignParams($designParams);
        return $this->_resolver->resolve(
            $this->getFallbackType(),
            $filePath,
            $designParams['area'],
            $designParams['themeModel'],
            $designParams['locale'],
            $module
        );
    }

    /**
     * @return string
     */
    protected function getFallbackType()
    {
        return \Magento\Framework\View\Design\Fallback\RulePool::TYPE_EMAIL_TEMPLATE;
    }
}
