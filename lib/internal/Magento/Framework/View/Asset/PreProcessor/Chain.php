<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * An object that's passed to preprocessors to carry current and original information for processing
 * Encapsulates complexity of all necessary context and parameters
 */
class Chain
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface
     */
    private $asset;

    /**
     * @var string
     */
    private $origContent;

    /**
     * @var string
     */
    private $origContentType;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $targetContentType;

    /**
     * @param \Magento\Framework\View\Asset\LocalInterface $asset
     * @param string $origContent
     * @param string $origContentType
     */
    public function __construct(\Magento\Framework\View\Asset\LocalInterface $asset, $origContent, $origContentType)
    {
        $this->asset = $asset;
        $this->origContent = $origContent;
        $this->content = $origContent;
        $this->origContentType = $origContentType;
        $this->contentType = $origContentType;
        $this->targetContentType = $asset->getContentType();
    }

    /**
     * Get asset object
     *
     * @return \Magento\Framework\View\Asset\LocalInterface
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * Get original content
     *
     * @return string
     */
    public function getOrigContent()
    {
        return $this->origContent;
    }

    /**
     * Get current content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set current content
     *
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get original content type
     *
     * @return string
     */
    public function getOrigContentType()
    {
        return $this->origContentType;
    }

    /**
     * Get current content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set current content type
     *
     * @param string $contentType
     * @return void
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Get the intended content type
     *
     * @return string
     */
    public function getTargetContentType()
    {
        return $this->targetContentType;
    }

    /**
     * Assert invariants
     *
     * Impose an integrity check to avoid generating mismatching content type and not leaving transient data behind
     *
     * @return void
     * @throws \LogicException
     */
    public function assertValid()
    {
        if ($this->contentType !== $this->targetContentType) {
            throw new \LogicException(
                "The requested asset type was '{$this->targetContentType}', but ended up with '{$this->contentType}'"
            );
        }
    }

    /**
     * Whether the contents or type have changed during the lifetime of the object
     *
     * @return bool
     */
    public function isChanged()
    {
        return $this->origContentType != $this->contentType || $this->origContent != $this->content;
    }
}
