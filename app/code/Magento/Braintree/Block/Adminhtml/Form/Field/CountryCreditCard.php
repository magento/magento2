<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Adminhtml\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class CountryCreditCard
 */
class CountryCreditCard extends AbstractFieldArray
{
    /**
     * @var Countries
     */
    protected $countryRenderer = null;

    /**
     * @var CcTypes
     */
    protected $ccTypesRenderer = null;
    
    /**
     * Returns renderer for country element
     * 
     * @return Countries
     */
    protected function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                Countries::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->countryRenderer;
    }

    /**
     * Returns renderer for country element
     * 
     * @return CcTypes
     */
    protected function getCcTypesRenderer()
    {
        if (!$this->ccTypesRenderer) {
            $this->ccTypesRenderer = $this->getLayout()->createBlock(
                CcTypes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->ccTypesRenderer;
    }

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'country_id',
            [
                'label'     => __('Country'),
                'renderer'  => $this->getCountryRenderer(),
            ]
        );
        $this->addColumn(
            'cc_types',
            [
                'label' => __('Allowed Credit Card Types'),
                'renderer'  => $this->getCcTypesRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $country = $row->getCountryId();
        $options = [];
        if ($country) {
            $options['option_' . $this->getCountryRenderer()->calcOptionHash($country)]
                = 'selected="selected"';

            $ccTypes = $row->getCcTypes();
            foreach ($ccTypes as $cardType) {
                $options['option_' . $this->getCcTypesRenderer()->calcOptionHash($cardType)]
                    = 'selected="selected"';
            }
        }
        $row->setData('option_extra_attrs', $options);
    }
}
