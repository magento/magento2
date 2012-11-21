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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab for Social Networking settings
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Mobile_Edit_Tab_Social
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);

        $data = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication()->getFormData();

        $yesNoValues = Mage::getModel('Mage_Backend_Model_Config_Source_Yesno')->toOptionArray();

        /**
         * Default values for social networks is DISABLED
         */
        $twitterStatus  = $facebookStatus = $linkedinStatus = 0;
        $noteText       = $this->__('Please <a href="%s" target="_blank">click here</a> to see how to setup and retrieve API credentials.');

        /**
         * Twitter fieldset options
         */
        $fieldsetTwitter = $form->addFieldset('twitter', array(
            'legend' => $this->__('Twitter API')
        ));

        if (isset($data['conf[native][socialNetworking][twitter][isActive]'])) {
            $twitterStatus = (int)$data['conf[native][socialNetworking][twitter][isActive]'];
        }

        $twitterActiveField = $fieldsetTwitter->addField(
            'conf/native/socialNetworking/twitter/isActive',
            'select',
            array(
                'label'     => $this->__('Enable Twitter'),
                'name'      => 'conf[native][socialNetworking][twitter][isActive]',
                'values'    => $yesNoValues,
                'value'     => $twitterStatus,
            )
        );

        if (isset($data['conf[native][socialNetworking][twitter][apiKey]'])) {
            $twitterApiKey = $data['conf[native][socialNetworking][twitter][apiKey]'];
        } else {
            $twitterApiKey = '';
        }

        $twitterApiKeyField = $fieldsetTwitter->addField(
            'conf/native/socialNetworking/twitter/apiKey',
            'text',
            array(
                'label'     => $this->__('Twitter API Key'),
                'name'      => 'conf[native][socialNetworking][twitter][apiKey]',
                'required'  => true,
                'value'     => $twitterApiKey
            )
        );

        if (isset($data['conf[native][socialNetworking][twitter][secretKey]'])) {
            $twitterSecretKey = $data['conf[native][socialNetworking][twitter][secretKey]'];
        } else {
            $twitterSecretKey = '';
        }

        $twitterSecretKeyField = $fieldsetTwitter->addField(
            'conf/native/socialNetworking/twitter/secretKey',
            'text',
            array(
                'label'     => $this->__('Twitter Secret Key'),
                'name'      => 'conf[native][socialNetworking][twitter][secretKey]',
                'required'  => true,
                'value'     => $twitterSecretKey
            )
        );

        $fieldsetTwitter->addField(
            'twitterNote',
            'note',
            array(
                'text'  => sprintf(
                    $noteText,
                    Mage::getStoreConfig(Mage_XmlConnect_Model_Application::XML_PATH_HOWTO_TWITTER_URL)
                ),
            )
        );

        /**
         * Facebook fieldset options
         */
        $fieldsetFacebook = $form->addFieldset('facebook', array(
            'legend' => $this->__('Facebook API'),
        ));

        if (isset($data['conf[native][socialNetworking][facebook][isActive]'])) {
            $facebookStatus = (int)$data['conf[native][socialNetworking][facebook][isActive]'];
        }

        $facebookActiveField = $fieldsetFacebook->addField(
            'conf/native/socialNetworking/facebook/isActive',
            'select',
            array(
                'label'     => $this->__('Enable Facebook'),
                'name'      => 'conf[native][socialNetworking][facebook][isActive]',
                'values'    => $yesNoValues,
                'value'     => $facebookStatus,
            )
        );

        if (isset($data['conf[native][socialNetworking][facebook][appID]'])) {
            $facebookAppID = $data['conf[native][socialNetworking][facebook][appID]'];
        } else {
            $facebookAppID = '';
        }

        $facebookAppIDField = $fieldsetFacebook->addField(
            'conf/native/socialNetworking/facebook/appID',
            'text',
            array(
                'label'     => $this->__('Facebook Application ID'),
                'name'      => 'conf[native][socialNetworking][facebook][appID]',
                'required'  => true,
                'value'     => $facebookAppID
            )
        );

        $fieldsetFacebook->addField(
            'facebookNote',
            'note',
            array(
                'text'  => sprintf(
                    $noteText,
                    Mage::getStoreConfig(Mage_XmlConnect_Model_Application::XML_PATH_HOWTO_FACEBOOK_URL)
                ),
            )
        );

        /**
         * LinkedIn fieldset options
         */
        $fieldsetLinkedin = $form->addFieldset('linkedin', array(
            'legend' => $this->__('LinkedIn API'),
        ));

        if (isset($data['conf[native][socialNetworking][linkedin][isActive]'])) {
            $linkedinStatus = (int)$data['conf[native][socialNetworking][linkedin][isActive]'];
        }

        $linkedinActiveField = $fieldsetLinkedin->addField(
            'conf/native/socialNetworking/linkedin/isActive',
            'select',
            array(
                'label'     => $this->__('Enable LinkedIn'),
                'name'      => 'conf[native][socialNetworking][linkedin][isActive]',
                'values'    => $yesNoValues,
                'value'     => $linkedinStatus,
            )
        );

        if (isset($data['conf[native][socialNetworking][linkedin][apiKey]'])) {
            $linkedinApiKey = $data['conf[native][socialNetworking][linkedin][apiKey]'];
        } else {
            $linkedinApiKey = '';
        }

        $linkedinApiKeyField = $fieldsetLinkedin->addField(
            'conf/native/socialNetworking/linkedin/apiKey',
            'text',
            array(
                'label'     => $this->__('LinkedIn API Key'),
                'name'      => 'conf[native][socialNetworking][linkedin][apiKey]',
                'required'  => true,
                'value'     => $linkedinApiKey
            )
        );

        if (isset($data['conf[native][socialNetworking][linkedin][secretKey]'])) {
            $linkedinSecretKey = $data['conf[native][socialNetworking][linkedin][secretKey]'];
        } else {
            $linkedinSecretKey = '';
        }

        $linkedinSecretKeyField = $fieldsetLinkedin->addField(
            'conf/native/socialNetworking/linkedin/secretKey',
            'text',
            array(
                'label'     => $this->__('LinkedIn Secret Key'),
                'name'      => 'conf[native][socialNetworking][linkedin][secretKey]',
                'required'  => true,
                'value'     => $linkedinSecretKey
            )
        );

        $fieldsetLinkedin->addField(
            'linkedinNote',
            'note',
            array(
                'text'  => sprintf(
                    $noteText,
                    Mage::getStoreConfig(Mage_XmlConnect_Model_Application::XML_PATH_HOWTO_LINKEDIN_URL)
                ),
            )
        );

        /**
         * Set field dependencies
         */
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
            /**
             * Facebook field dependencies
             */
            ->addFieldMap($facebookActiveField->getHtmlId(), $facebookActiveField->getName())
            ->addFieldMap($facebookAppIDField->getHtmlId(), $facebookAppIDField->getName())
            ->addFieldDependence(
                $facebookAppIDField->getName(),
                $facebookActiveField->getName(),
            1)
            /**
             * Twitter field dependencies
             */
            ->addFieldMap($twitterApiKeyField->getHtmlId(), $twitterApiKeyField->getName())
            ->addFieldMap($twitterActiveField->getHtmlId(), $twitterActiveField->getName())
            ->addFieldMap($twitterSecretKeyField->getHtmlId(), $twitterSecretKeyField->getName())
            ->addFieldDependence(
                $twitterApiKeyField->getName(),
                $twitterActiveField->getName(),
            1)
            ->addFieldDependence(
                $twitterSecretKeyField->getName(),
                $twitterActiveField->getName(),
            1)
            /**
             * LinkedIn field dependencies
             */
            ->addFieldMap($linkedinApiKeyField->getHtmlId(), $linkedinApiKeyField->getName())
            ->addFieldMap($linkedinActiveField->getHtmlId(), $linkedinActiveField->getName())
            ->addFieldMap($linkedinSecretKeyField->getHtmlId(), $linkedinSecretKeyField->getName())
            ->addFieldDependence(
                $linkedinApiKeyField->getName(),
                $linkedinActiveField->getName(),
            1)
            ->addFieldDependence(
                $linkedinSecretKeyField->getName(),
                $linkedinActiveField->getName(),
            1)
        );

        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Social Networking');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Social Networking');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool) !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getNewApplication();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
