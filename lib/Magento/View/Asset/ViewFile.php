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
namespace Magento\View\Asset;

/**
 * Page asset representing a view file
 */
class ViewFile implements MergeableInterface
{
    /**
     * View URL
     *
     * @var \Magento\View\Url
     */
    protected $viewUrl;

    /**
     * File
     *
     * @var string
     */
    protected $file;

    /**
     * Content type
     *
     * @var string
     */
    protected $contentType;

    /**
     * Constructor
     *
     * @param \Magento\View\Url $viewUrl
     * @param string $file
     * @param string $contentType
     * @throws \InvalidArgumentException
     */
    public function __construct(\Magento\View\Url $viewUrl, $file, $contentType)
    {
        if (empty($file)) {
            throw new \InvalidArgumentException("Parameter 'file' must not be empty");
        }
        $this->viewUrl = $viewUrl;
        $this->file = $file;
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->viewUrl->getViewFileUrl($this->file);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        return $this->viewUrl->getViewFilePublicPath($this->file);
    }
}
