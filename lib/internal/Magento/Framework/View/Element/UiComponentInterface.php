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
namespace Magento\Framework\View\Element;

use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use \Magento\Framework\View\Element\UiComponent\Context as RenderContext;

/**
 * Class UiComponentInterface
 */
interface UiComponentInterface extends BlockInterface
{
    /**
     * Update component data
     *
     * @param array $arguments
     * @return string
     */
    public function update(array $arguments = []);

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare();

    /**
     * Render component
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = []);

    /**
     * Render label
     *
     * @return mixed|string
     */
    public function renderLabel();

    /**
     * Getting template for rendering content
     *
     * @return string|false
     */
    public function getContentTemplate();

    /**
     * Getting template for rendering label
     *
     * @return string|false
     */
    public function getLabelTemplate();

    /**
     * Getting instance name
     *
     * @return string
     */
    public function getName();

    /**
     * Getting parent name component instance
     *
     * @return string
     */
    public function getParentName();

    /**
     * Get render context
     *
     * @return RenderContext
     */
    public function getRenderContext();

    /**
     * Get elements
     *
     * @return UiComponentInterface[]
     */
    public function getElements();

    /**
     * Set elements
     *
     * @param array $elements
     * @return mixed
     */
    public function setElements(array $elements);

    /**
     * Get configuration builder
     *
     * @return ConfigBuilderInterface
     */
    public function getConfigBuilder();
}
