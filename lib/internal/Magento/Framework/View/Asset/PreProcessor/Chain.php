<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\LocalInterface;

/**
 * An object that's passed to preprocessors to carry current and original information for processing
 * Encapsulates complexity of all necessary context and parameters
 *
 * @api
 * @since 2.0.0
 */
class Chain
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $compatibleTypes;

    /**
     * @var LocalInterface
     * @since 2.0.0
     */
    private $asset;

    /**
     * @var string
     * @since 2.0.0
     */
    private $origContent;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $origContentType;

    /**
     * @var string
     * @since 2.0.0
     */
    private $content;

    /**
     * @var string
     * @since 2.0.0
     */
    private $contentType;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $targetContentType;

    /**
     * @var null|string
     * @since 2.0.0
     */
    protected $targetAssetPath;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $origAssetPath;

    /**
     * @param LocalInterface $asset
     * @param string $origContent
     * @param string $origContentType
     * @param string $origAssetPath
     * @param array $compatibleTypes
     * @since 2.0.0
     */
    public function __construct(
        LocalInterface $asset,
        $origContent,
        $origContentType,
        $origAssetPath,
        array $compatibleTypes = []
    ) {
        $this->asset = $asset;
        $this->origContent = $origContent;
        $this->content = $origContent;
        $this->origContentType = $origContentType;
        $this->contentType = $origContentType;
        $this->targetContentType = $asset->getContentType();
        $this->targetAssetPath = $asset->getPath();
        $this->origAssetPath = $origAssetPath;
        $this->compatibleTypes = $compatibleTypes;
    }

    /**
     * Get asset object
     *
     * @return LocalInterface
     * @since 2.0.0
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * Get original content
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrigContent()
    {
        return $this->origContent;
    }

    /**
     * Get current content
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get original content type
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrigContentType()
    {
        return $this->origContentType;
    }

    /**
     * Get current content type
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Get the intended content type
     *
     * @return string
     * @since 2.0.0
     */
    public function getTargetContentType()
    {
        return $this->targetContentType;
    }

    /**
     * Get the target asset path
     *
     * @return string
     * @since 2.0.0
     */
    public function getTargetAssetPath()
    {
        return $this->targetAssetPath;
    }

    /**
     * Assert invariants
     *
     * Impose an integrity check to avoid generating mismatching content type and not leaving transient data behind
     *
     * @return void
     * @throws \LogicException
     * @since 2.0.0
     */
    public function assertValid()
    {
        if ($this->contentType !== $this->targetContentType
                && empty($this->compatibleTypes[$this->targetContentType][$this->contentType])) {
            throw new \LogicException(
                "The requested asset type was '{$this->targetContentType}', but ended up with '{$this->contentType}'"
            );
        }
    }

    /**
     * Whether the contents or type have changed during the lifetime of the object
     *
     * @return bool
     * @since 2.0.0
     */
    public function isChanged()
    {
        return $this->origContentType != $this->contentType || $this->origContent != $this->content;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getOrigAssetPath()
    {
        return $this->origAssetPath;
    }
}
