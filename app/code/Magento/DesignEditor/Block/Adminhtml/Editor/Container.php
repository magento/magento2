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
 * Editor toolbar
 *
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Container setTheme(\Magento\View\Design\ThemeInterface $theme)
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor;

class Container extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Frame Url
     *
     * @var string
     */
    protected $_frameUrl;

    /**
     * Add elements in layout
     */
    protected function _prepareLayout()
    {
        $this->addButton('back_button', array(
            'label'   => __('Back'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*') . '\')',
            'class'   => 'back'
        ));

        parent::_prepareLayout();
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Store Designer');
    }

    /**
     * @param string $url
     *
     * @return \Magento\DesignEditor\Block\Adminhtml\Editor\Container
     */
    public function setFrameUrl($url)
    {
        $this->_frameUrl = $url;
        return $this;
    }

    /**
     * Retrieve frame url
     *
     * @return string
     */
    public function getFrameUrl()
    {
        return $this->_frameUrl;
    }
}
