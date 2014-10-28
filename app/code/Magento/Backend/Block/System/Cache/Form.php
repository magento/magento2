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
namespace Magento\Backend\Block\System\Cache;

/**
 * Cache management form page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Initialize cache management form
     *
     * @return $this
     */
    public function initForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('cache_enable', array('legend' => __('Cache Control')));

        $fieldset->addField(
            'all_cache',
            'select',
            array(
                'name' => 'all_cache',
                'label' => '<strong>' . __('All Cache') . '</strong>',
                'value' => 1,
                'options' => array(
                    '' => __('No change'),
                    'refresh' => __('Refresh'),
                    'disable' => __('Disable'),
                    'enable' => __('Enable')
                )
            )
        );

        foreach ($this->_coreData->getCacheTypes() as $type => $label) {
            $fieldset->addField(
                'enable_' . $type,
                'checkbox',
                array(
                    'name' => 'enable[' . $type . ']',
                    'label' => __($label),
                    'value' => 1,
                    'checked' => (int)$this->_cacheState->isEnabled($type)
                )
            );
        }
        $this->setForm($form);
        return $this;
    }
}
