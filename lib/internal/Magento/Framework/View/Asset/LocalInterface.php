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
