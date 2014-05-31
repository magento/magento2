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
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

/**
 * Block that renders CSS tab
 */
class Css extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_designEditorHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\DesignEditor\Helper\Data $designEditorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Helper\Data $designEditorHelper,
        array $data = array()
    ) {
        $this->_designEditorHelper = $designEditorHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get CSS file assets
     *
     * Note: someone must set them in the first place
     *
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     */
    public function getAssets()
    {
        return $this->_getData('assets');
    }

    /**
     * Get url to download CSS file
     *
     * @param string $fileId
     * @param int $themeId
     * @return string
     */
    public function getDownloadUrl($fileId, $themeId)
    {
        return $this->getUrl(
            'adminhtml/system_design_theme/downloadCss',
            ['theme_id' => $themeId, 'file' => $this->_designEditorHelper->urlEncode($fileId)]
        );
    }

    /**
     * Check if files group needs "add" button
     *
     * @return false
     */
    public function hasAddButton()
    {
        return false;
    }

    /**
     * Check if files group needs download buttons next to each file
     *
     * @return true
     */
    public function hasDownloadButton()
    {
        return true;
    }
}
