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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for contents
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab;

class Contents extends \Magento\Connect\Block\Adminhtml\Extension\Custom\Edit\Tab\AbstractTab
{
    /**
     * Extension factory
     *
     * @var \Magento\Connect\Model\ExtensionFactory
     */
    protected $_extensionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Connect\Model\Session $session
     * @param \Magento\Connect\Model\ExtensionFactory $extensionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Connect\Model\Session $session,
        \Magento\Connect\Model\ExtensionFactory $extensionFactory,
        array $data = array()
    ) {
        $this->_extensionFactory = $extensionFactory;
        parent::__construct($context, $registry, $formFactory, $session, $data);
    }

    /**
     * Retrieve list of targets
     *
     * @return array
     */
    public function getMageTargets()
    {
        $targets = $this->_extensionFactory->create()->getLabelTargets();
        if (!is_array($targets)) {
            $targets = array();
        }
        return $targets;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Contents');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Contents');
    }
}
