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
namespace Magento\Backend\Block\Widget\View;

/**
 * Magento_Backend view container block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @deprecated is not used in code
 */
class Container extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_objectId = 'id';

    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Backend';

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/view/container.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->add(
            'back',
            array(
                'label' => __('Back'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/') . '\'',
                'class' => 'back'
            )
        );

        $this->buttonList->add(
            'edit',
            array(
                'label' => __('Edit'),
                'class' => 'edit',
                'onclick' => 'window.location.href=\'' . $this->getEditUrl() . '\''
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $blockName = $this->_blockGroup . '\\Block\\' . str_replace(
            ' ',
            '\\',
            ucwords(str_replace('\\', ' ', $this->_controller))
        ) . '\\View\\Plane';

        $this->setChild('plane', $this->getLayout()->createBlock($blockName));

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    /**
     * @return string
     */
    public function getViewHtml()
    {
        return $this->getChildHtml('plane');
    }
}
