<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Reader\Source\Deployed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\DocumentRoot as BaseDocumentRoot;

/**
 * Document root detector.
 *
 * @api
 * @since 101.0.0
 *
 * @deprecated Use new implementation
 * @see \Magento\Framework\Config\DocumentRoot
 */
class DocumentRoot
{
    /**
     * @var BaseDocumentRoot
     */
    private $documentRoot;

    /**
     * @param DeploymentConfig $config
     * @param BaseDocumentRoot $documentRoot
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(DeploymentConfig $config, BaseDocumentRoot $documentRoot = null)
    {
        $this->documentRoot = $documentRoot ?: ObjectManager::getInstance()->get(BaseDocumentRoot::class);
    }

    /**
     * A shortcut to load the document root path from the DirectoryList.
     *
     * @return string
     * @since 101.0.0
     */
    public function getPath()
    {
        return $this->documentRoot->getPath();
    }

    /**
     * Checks if root folder is /pub.
     *
     * @return bool
     * @since 101.0.0
     */
    public function isPub()
    {
        return $this->documentRoot->isPub();
    }
}
