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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Block\Recurring\Profile;

/**
 * Recurring profile view grid
 */
class Grid extends \Magento\Sales\Block\Recurring\Profiles
{
    /**
     * @var \Magento\Core\Model\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Sales\Model\Recurring\Profile
     */
    protected $_recurringProfile;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Profiles collection
     *
     * @var \Magento\Sales\Model\Resource\Recurring\Profile\Collection
     */
    protected $_profiles = null;

    /**
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Sales\Model\Recurring\Profile $profile
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Core\Helper\Data $coreData
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Block\Template\Context $context,
        \Magento\Sales\Model\Recurring\Profile $profile,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Core\Helper\Data $coreData,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $data);
        $this->_recurringProfile = $profile;
        $this->_registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_locale = $locale;
    }

    /**
     * Instantiate profiles collection
     *
     * @param array|int|string $fields
     */
    protected function _prepareProfiles($fields = '*')
    {
        $this->_profiles = $this->_recurringProfile->getCollection()
            ->addFieldToFilter('customer_id', $this->_registry->registry('current_customer')->getId())
            ->addFieldToSelect($fields)
            ->setOrder('profile_id', 'desc');
    }

    /**
     * Prepare grid data
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_prepareProfiles(array('reference_id', 'state', 'created_at', 'updated_at', 'method_code'));

        $pager = $this->getLayout()->createBlock('Magento\Page\Block\Html\Pager')
            ->setCollection($this->_profiles)->setIsOutputRequired(false);
        $this->setChild('pager', $pager);

        $this->setGridColumns(array(
            new \Magento\Object(array(
                'index' => 'reference_id',
                'title' => $this->_recurringProfile->getFieldLabel('reference_id'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new \Magento\Object(array(
                'index' => 'state',
                'title' => $this->_recurringProfile->getFieldLabel('state'),
            )),
            new \Magento\Object(array(
                'index' => 'created_at',
                'title' => $this->_recurringProfile->getFieldLabel('created_at'),
                'is_nobr' => true,
                'width' => 1,
                'is_amount' => true,
            )),
            new \Magento\Object(array(
                'index' => 'updated_at',
                'title' => $this->_recurringProfile->getFieldLabel('updated_at'),
                'is_nobr' => true,
                'width' => 1,
            )),
            new \Magento\Object(array(
                'index' => 'method_code',
                'title' => $this->_recurringProfile->getFieldLabel('method_code'),
                'is_nobr' => true,
                'width' => 1,
            )),
        ));

        $profiles = array();
        $store = $this->_storeManager->getStore();
        foreach ($this->_profiles as $profile) {
            $profile->setStore($store)->setLocale($this->_locale);
            $profiles[] = new \Magento\Object(array(
                'reference_id' => $profile->getReferenceId(),
                'reference_id_link_url' => $this->getUrl(
                    'sales/recurring_profile/view/',
                    array('profile' => $profile->getId())
                ),
                'state'       => $profile->renderData('state'),
                'created_at'  => $this->formatDate($profile->getData('created_at'), 'medium', true),
                'updated_at'  => $profile->getData('updated_at')
                    ? $this->formatDate($profile->getData('updated_at'), 'short', true)
                    : '',
                'method_code' => $profile->renderData('method_code'),
            ));
        }
        if ($profiles) {
            $this->setGridElements($profiles);
        }
    }
}
