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
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector;

/**
 * Theme selectors tabs container
 *
 * @method int getThemeId()
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreView extends \Magento\Backend\Block\Template
{
    /**
     * Website collection
     *
     * @var \Magento\Store\Model\Resource\Website\Collection
     */
    protected $_websiteCollection;

    /**
     * @var \Magento\Theme\Model\Config\Customization
     */
    protected $_customizationConfig;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\Resource\Website\Collection $websiteCollection
     * @param \Magento\Theme\Model\Config\Customization $customizationConfig
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\Resource\Website\Collection $websiteCollection,
        \Magento\Theme\Model\Config\Customization $customizationConfig,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
        $this->_websiteCollection = $websiteCollection;
        $this->_customizationConfig = $customizationConfig;

        parent::__construct($context, $data);
    }

    /**
     * Get website collection with stores and store-views joined
     *
     * @return \Magento\Store\Model\Resource\Website\Collection
     */
    public function getCollection()
    {
        return $this->_websiteCollection->joinGroupAndStore();
    }

    /**
     * Get website, stores and store-views
     *
     * @return \Magento\Store\Model\Resource\Website\Collection
     */
    public function getWebsiteStructure()
    {
        $structure = array();
        $website = null;
        $store = null;
        $storeView = null;
        /** @var $row \Magento\Store\Model\Website */
        foreach ($this->getCollection() as $row) {
            $website = $row->getName();
            $store = $row->getGroupTitle();
            $storeView = $row->getStoreTitle();
            if (!isset($structure[$website])) {
                $structure[$website] = array();
            }
            if (!isset($structure[$website][$store])) {
                $structure[$website][$store] = array();
            }
            $structure[$website][$store][$storeView] = (int)$row->getStoreId();
        }

        return $structure;
    }

    /**
     * Get assign to multiple storeview button
     *
     * @return string
     */
    public function getAssignNextButtonHtml()
    {
        /** @var $assignSaveButton \Magento\Backend\Block\Widget\Button */
        $assignSaveButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        $assignSaveButton->setData(
            array(
                'label' => __('Assign'),
                'class' => 'action-save primary',
                'data_attribute' => array(
                    'mage-init' => array(
                        'button' => array('event' => 'assign-confirm', 'target' => 'body', 'eventData' => array())
                    )
                )
            )
        );

        return $assignSaveButton->toHtml();
    }

    /**
     * Get an array of stores grouped by theme customization it uses.
     *
     * The structure is the following:
     *   array(
     *      theme_id => array(store_id)
     *   )
     *
     * @return array
     */
    protected function _getStoresByThemes()
    {
        $assignedThemeIds = array_map(
            function ($theme) {
                return $theme->getId();
            },
            $this->_customizationConfig->getAssignedThemeCustomizations()
        );

        $storesByThemes = array();
        foreach ($this->_customizationConfig->getStoresByThemes() as $themeId => $stores) {
            /* NOTE
               We filter out themes not included to $assignedThemeIds array so we only get actually "assigned"
               themes. So if theme is assigned to store or website and used by store-view only via config fall-back
               mechanism it will not get to the resulting $storesByThemes array.
               */
            if (!in_array($themeId, $assignedThemeIds)) {
                continue;
            }

            $storesByThemes[$themeId] = array();
            /** @var $store \Magento\Store\Model\Store */
            foreach ($stores as $store) {
                $storesByThemes[$themeId][] = (int)$store->getId();
            }
        }

        return $storesByThemes;
    }

    /**
     * Get the flag if there are multiple store-views in Magento
     *
     * @return bool
     */
    protected function _hasMultipleStores()
    {
        $isMultipleMode = false;
        $tmpStore = null;
        foreach ($this->_customizationConfig->getStoresByThemes() as $stores) {
            /** @var $store \Magento\Store\Model\Store */
            foreach ($stores as $store) {
                if ($tmpStore === null) {
                    $tmpStore = $store->getId();
                } elseif ($tmpStore != $store->getId()) {
                    $isMultipleMode = true;
                    break 2;
                }
            }
        }

        return $isMultipleMode;
    }

    /**
     * Get options for JS widget vde.storeSelector
     *
     * @return string
     */
    public function getOptionsJson()
    {
        $options = array();
        $options['storesByThemes'] = $this->_getStoresByThemes();
        $options['assignUrl'] = $this->getUrl(
            'adminhtml/*/assignThemeToStore',
            array('theme_id' => $this->getThemeId())
        );
        $options['afterAssignUrl'] = $this->getUrl('adminhtml/*/index');
        $options['hasMultipleStores'] = $this->_hasMultipleStores();

        $options['actionOnAssign'] = $this->getData('actionOnAssign');
        $options['afterAssignOpen'] = false;

        /** @var $helper \Magento\Core\Helper\Data */
        $helper = $this->_coreHelper;

        return $helper->jsonEncode($options);
    }
}
