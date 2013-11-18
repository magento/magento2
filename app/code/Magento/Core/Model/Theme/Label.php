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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme_Label class used for system configuration
 */
namespace Magento\Core\Model\Theme;

class Label
{
    /**
     * Labels collection array
     *
     * @var array
     */
    protected $_labelsCollection;

    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory
     */
    public function __construct(\Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Return labels collection array
     *
     * @param bool|string $label add empty values to result with specific label
     * @return array
     */
    public function getLabelsCollection($label = false)
    {
        if (!$this->_labelsCollection) {
            $themeCollection = $this->_collectionFactory->create();
            $themeCollection->setOrder('theme_title', \Magento\Data\Collection::SORT_ORDER_ASC);
            $themeCollection->filterVisibleThemes()->addAreaFilter(\Magento\Core\Model\App\Area::AREA_FRONTEND);
            $this->_labelsCollection = $themeCollection->toOptionArray();
        }
        $options = $this->_labelsCollection;
        if ($label) {
            array_unshift($options, array('value' => '', 'label' => $label));
        }
        return $options;
    }

    /**
     * Return labels collection for backend system configuration with empty value "No Theme"
     *
     * @return array
     */
    public function getLabelsCollectionForSystemConfiguration()
    {
        return $this->getLabelsCollection(__('-- No Theme --'));
    }
}
