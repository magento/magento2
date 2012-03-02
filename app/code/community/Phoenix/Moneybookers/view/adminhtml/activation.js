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
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2012 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

Event.observe(window, 'load', function() {
   initMoneybookers();
});

Moneybookers = Class.create();
Moneybookers.prototype = {
    initialize: function(bannerUrl, activateemailUrl, checksecretUrl, checkemailUrl){
        this.bannerUrl = bannerUrl;
        this.activateemailUrl = activateemailUrl;
        this.checksecretUrl = checksecretUrl;
        this.checkemailUrl = checkemailUrl;

        this.txtBtnStatus0 = this.translate('Validate Email');
        this.txtBtnStatus1 = this.translate('Activate Moneybookers Quick Checkout');
        this.txtBtnStatus2 = this.translate('Validate Secret Word');
        this.txtErrStatus0 = this.translate('This email address is not registered.');
        this.txtErrStatus2 = this.translate('This Secret Word is incorrect. After activation Moneybookers will give you access to a new section in your Moneybookers account called "Merchant tools". Please choose a secret word (do not use your password for this) and provide it in your Moneybookers admin area and above.');
        this.txtInfStatus0 = this.translate('<strong><a href="http://www.moneybookers.com/" target="_blank">Moneybookers</a></strong> is an all in one payments solution that enables a merchant to accept debit and credit card payments, bank transfers and the largest range of local payments directly on your website.<ul style="list-style-position: inside;list-style-type: disc;"><li>Widest network of international and local payment options in the world.</li><li>One interface including payments, banking and marketing.</li><li>Direct payments without the need for direct registration.</li><li>Moneybookers stands for a highly converting payment gateway that turns payment processing into a simple, fast and customer friendly operation.</li><li>Highly competitive rates. Please <a href="http://www.moneybookers.com/app/help.pl?s=m_fees" target="_blank">click here</a> for more detailed information.</li></ul>') + '<img src="' + this.bannerUrl + '" alt="" />';
        this.txtInfStatus1 = this.translate('<strong>Moneybookers Quick Checkout</strong> enables you to take <strong>direct</strong> payments from credit cards, debit cards and over 50 other local payment options in over 200 countries for customers without an existing Moneybookers eWallet.');
        this.txtNotSavechanges = this.translate('Please save the configuration before continuing.');
        this.txtNotStatus0 = this.translate('Email was validated by Moneybookers.');
        this.txtNotStatus1 = this.translate('Activation email was sent to Moneybookers. Please be aware that the verification process to use Moneybookers Quick Checkout takes some time. You will be contacted by Moneybookers when the verification process has been completed.');
        this.txtNotStatus2 = this.translate('Secret Word was validated by Moneybookers. Your installation is completed and you are ready to receive international and local payments.');

        $("moneybookers_settings_moneybookers_email").setAttribute("onchange", "moneybookers.setStatus(0); moneybookers.changeUi(); document.getElementById('moneybookers_settings_customer_id').value = ''; document.getElementById('moneybookers_settings_customer_id_hidden').value = '';");
        $("moneybookers_settings_customer_id").disabled = true;
        $("moneybookers_settings_customer_id_hidden").name = document.getElementById("moneybookers_settings_customer_id").name;
        $("moneybookers_settings_customer_id_hidden").value = document.getElementById("moneybookers_settings_customer_id").value;
        $("moneybookers_settings_secret_key").setAttribute("onchange", "moneybookers.setStatus(2); moneybookers.changeUi();");

        if (this.isStoreView()) {
            this.infoOrig = {
                email: $("moneybookers_settings_moneybookers_email").value,
                customerId: $("moneybookers_settings_customer_id").value,
                key: $("moneybookers_settings_secret_key").value,
                status: this.getStatus(),
                useDefult: $("moneybookers_settings_moneybookers_email_inherit").checked
            };
            var defaults = $$("#row_moneybookers_settings_customer_id .use-default, #row_moneybookers_settings_secret_key .use-default, #row_moneybookers_settings_activationstatus .use-default");
            if (Object.isArray(defaults)) {
                for (var i=0; i<defaults.length; i++) {
                    defaults[i].hide();
                }
            }
            $("moneybookers_settings_moneybookers_email_inherit").setAttribute("onchange", "moneybookers.changeStore();");
        }

        this.changeUi();
    },

    translate: function(text) {
        try {
            if(Translator){
               return Translator.translate(text);
            }
        }
        catch(e){}
        return text;
    },

    button: function () {
        var status, response, result;
        status = this.getStatus();
        if (status < 1) {
            response = this.getHttp(this.checkemailUrl + "?email=" + $("moneybookers_settings_moneybookers_email").value);
            result = response.split(',');
            if (result[0] == "OK") {
                $("moneybookers_settings_customer_id").value = result[1];
                $("moneybookers_settings_customer_id_hidden").value = result[1];
                this.setStatus(1);
                status = 1;
                alert(this.txtNotStatus0);
            }
            else {
                $("moneybookers_settings_customer_id").value = "";
                alert(this.txtErrStatus0 + "\n("+response+")");
            }
        }
        if (status == 1) {
            this.getHttp(this.activateemailUrl);
            this.setStatus(2);
            alert(this.txtNotStatus1);
            this.alertSaveChanges();
        }
        if (status == 2) {
            response = this.getHttp(this.checksecretUrl + "?email=" + $("moneybookers_settings_moneybookers_email").value
                + "&secret=" + $("moneybookers_settings_secret_key").value
                + "&cust_id=" + $("moneybookers_settings_customer_id").value);
            if (response == "OK") {
                this.setStatus(3);
                alert(this.txtNotStatus2);
                this.alertSaveChanges();
            }
            else {
                alert(this.txtErrStatus2 + "\n("+response+")");
            }
        }
    },

    alertSaveChanges: function () {
        $("moneybookers_multifuncbutton").style.display = "none";
        alert(this.txtNotSavechanges);
    },

    getHttp: function (url) {
        var response;
        new Ajax.Request(
            url,
            {
                method:       "get",
                onComplete:   function(transport) {response = transport.responseText;},
                asynchronous: false
            });
        return response;
    },

    getInteger: function (number) {
        number = parseInt(number);
        if (isNaN(number)) return 0;
        return number;
    },

    getStatus: function () {
        var status = this.getInteger($("moneybookers_settings_activationstatus").value);
        if (status == 1 && $("moneybookers_settings_customer_id").value != '' && $("moneybookers_settings_secret_key").value == '') {
            status = 2;
            this.setStatus(status);
        }
        return status;
    },

    setStatus: function (number) {
        number = this.getInteger(number);
        if (number < 0) number = 0;
        else if (number > 3) number = 3;
        $("moneybookers_settings_activationstatus").value = number;
    },
    changeUi: function () {
        var status = this.getStatus();
        if (status < 1) {
            $("moneybookers_inf_div").update(this.txtInfStatus0);
            $("moneybookers_multifuncbutton_label").update(this.txtBtnStatus0);
        }
        if (status == 1) {
            $("moneybookers_inf_div").update(this.txtInfStatus1);
            $("moneybookers_multifuncbutton_label").update(this.txtBtnStatus1);
        }
        if (status < 2) {
            $("moneybookers_inf_div").style.display = "block";
            $("moneybookers_settings_secret_key").disabled = true;
        }
        if (status == 2) {
            $("moneybookers_multifuncbutton_label").update(this.txtBtnStatus2);
            if (this.isStoreView()) {
                $("moneybookers_settings_secret_key").enable();
                $("moneybookers_settings_secret_key_inherit").removeAttribute('checked');
            }
        }
        if (status > 2) {
            $("moneybookers_multifuncbutton").style.display = "none";
        } else {
            $("moneybookers_multifuncbutton").style.display = "block";
        }
    },

    changeStore: function () {
        if (!$("moneybookers_settings_moneybookers_email_inherit").checked) {
            if (this.infoOrig.useDefult) {
                $("moneybookers_settings_customer_id_inherit").click();
                $("moneybookers_settings_customer_id").clear();
                $("moneybookers_settings_secret_key_inherit").click();
                $("moneybookers_settings_secret_key").clear();
                $("moneybookers_settings_activationstatus_inherit").click();
                this.setStatus(0);
            }
        }
        else {
            if (this.infoOrig.useDefult) {
                $("moneybookers_settings_customer_id").setValue(this.infoOrig.customerId);
                $("moneybookers_settings_customer_id_inherit").click();
                $("moneybookers_settings_secret_key").setValue(this.infoOrig.key);
                $("moneybookers_settings_secret_key_inherit").click();
                $("moneybookers_settings_activationstatus_inherit").click();
                this.setStatus(this.infoOrig.status);
            }
        }
        this.changeUi();
    },

    isStoreView: function() {
        return $("moneybookers_settings_moneybookers_email_inherit") != undefined;
    }
};
