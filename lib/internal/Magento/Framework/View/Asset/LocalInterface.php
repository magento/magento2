<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface of an asset with locally accessible source file
 */
interface LocalInterface extends AssetInterface
{
    /**
     * Get original source file where the asset contents can be read from
     *
     * Returns absolute path to file in local file system
     *
     * @return string
     */
    public function getSourceFile();

    /**
     * Get content of a local asset
     *
     * @return string
     */
    public function getContent();

    /**
     * Get an invariant relative path to file
     *
     * @return string
     */
    public function getFilePath();

    /**
     * Get context of the asset that contains data necessary to build an absolute path or URL to the file
     *
     * @return ContextInterface
     */
    public function getContext();

    /**
     * Get the module context of file path
     *
     * @return string
     */
    public function getModule();

    /**
     * Get a relative "context" path to the asset file
     *
     * This path includes both invariant and context part that can serve as an identifier of the file in current context
     *
     * @return string
     */
    public function getPath();
}
