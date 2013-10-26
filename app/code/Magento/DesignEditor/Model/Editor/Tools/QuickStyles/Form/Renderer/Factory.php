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
 * @category    Magento
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block that renders JS tab
 *
 * @method \Magento\View\Design\ThemeInterface getTheme()
 * @method setTheme($theme)
 *
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Form\Renderer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Factory
{
    /**
     * Layout model
     *
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * List of form elements and renderers for them
     *
     * @var array
     */
    protected $_rendererByElement = array(
        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Column'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Column',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ColorPicker'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\ColorPicker',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Logo'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Composite',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Font'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Font',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\LogoUploader'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\LogoUploader',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\Background'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Composite',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\FontPicker'
            => 'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\BackgroundUploader'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\BackgroundUploader',

        'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Element\ImageUploader'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\ImageUploader',

        'Magento\Data\Form\Element\Checkbox'
            => 'Magento\DesignEditor\Block\Adminhtml\Editor\Form\Renderer\Checkbox'
    );

    /**
     * Storage of renderers that could be shared between elements
     *
     * @see self::create()
     * @var array
     */
    protected $_sharedRenderers = array();

    /**
     * @param \Magento\View\LayoutInterface $layout
     */
    public function __construct(\Magento\View\LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    /**
     * Get renderer for element
     *
     * @param string $elementClassName
     * @param string $rendererName
     * @return \Magento\Data\Form\Element\Renderer\RendererInterface
     * @throws \Magento\Core\Exception
     */
    public function create($elementClassName, $rendererName)
    {
        if (!isset($this->_rendererByElement[$elementClassName])) {
            throw new \Magento\Core\Exception(
                sprintf('No renderer registered for elements of class "%s"', $elementClassName)
            );
        }
        $rendererClass = $this->_rendererByElement[$elementClassName];
        $renderer = $this->_layout->createBlock($rendererClass, $rendererName);

        return $renderer;
    }

    /**
     * Renderer can be shared if it's guaranteed that no nested elements that use this renderer again.
     * For example:
     *   If Renderer01 used to render Element01 that should render some other Element02 using same Renderer01 it will
     *   cause an error. Cause internal Renderer01 property '_element' will be overwritten with Element02 during
     *   reuse of renderer and then will not be restored.
     */
    public function getSharedInstance($elementClassName, $rendererName = null)
    {
        $rendererClass = $this->_rendererByElement[$elementClassName];
        if (isset($this->_sharedRenderers[$rendererClass])) {
            $renderer = $this->_sharedRenderers[$rendererClass];
        } else {
            if ($rendererName === null) {
                $rendererName = uniqid('renderer-');
            }
            $renderer = $this->create($elementClassName, $rendererName);
        }

        return $renderer;
    }
}
