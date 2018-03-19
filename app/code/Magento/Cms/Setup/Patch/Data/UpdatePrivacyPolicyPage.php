<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class UpdatePrivacyPolicyPage
 * @package Magento\Cms\Setup\Patch
 */
class UpdatePrivacyPolicyPage implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * UpdatePrivacyPolicyPage constructor.
     * @param PageFactory $pageFactory
     */
    public function __construct(
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
                $newPageContent = <<<EOD
<div class="privacy-policy cms-content">
    <div class="message info">
        <span>
            Please replace this text with you Privacy Policy.
            Please add any additional cookies your website uses below (e.g. Google Analytics).
        </span>
    </div>
    <p>
        This privacy policy sets out how this website (hereafter "the Store") uses and protects any information that
        you give the Store while using this website. The Store is committed to ensuring that your privacy is protected.
        Should we ask you to provide certain information by which you can be identified when using this website, then
        you can be assured that it will only be used in accordance with this privacy statement. The Store may change
        this policy from time to time by updating this page. You should check this page from time to time to ensure
        that you are happy with any changes.
    </p>
    <h2>What we collect</h2>
    <p>We may collect the following information:</p>
    <ul>
        <li>name</li>
        <li>contact information including email address</li>
        <li>demographic information such as postcode, preferences and interests</li>
        <li>other information relevant to customer surveys and/or offers</li>
    </ul>
    <p>
        For the exhaustive list of cookies we collect see the <a href="#list">List of cookies we collect</a> section.
    </p>
    <h2>What we do with the information we gather</h2>
    <p>
        We require this information to understand your needs and provide you with a better service,
        and in particular for the following reasons:
    </p>
    <ul>
        <li>Internal record keeping.</li>
        <li>We may use the information to improve our products and services.</li>
        <li>
            We may periodically send promotional emails about new products, special offers or other information which we
            think you may find interesting using the email address which you have provided.
        </li>
        <li>
            From time to time, we may also use your information to contact you for market research purposes.
            We may contact you by email, phone, fax or mail. We may use the information to customise the website
            according to your interests.
        </li>
    </ul>
    <h2>Security</h2>
    <p>
        We are committed to ensuring that your information is secure. In order to prevent unauthorised access or
        disclosure, we have put in place suitable physical, electronic and managerial procedures to safeguard and
        secure the information we collect online.
    </p>
    <h2>How we use cookies</h2>
    <p>
        A cookie is a small file which asks permission to be placed on your computer's hard drive.
        Once you agree, the file is added and the cookie helps analyse web traffic or lets you know when you visit
        a particular site. Cookies allow web applications to respond to you as an individual. The web application
        can tailor its operations to your needs, likes and dislikes by gathering and remembering information about
        your preferences.
    </p>
    <p>
        We use traffic log cookies to identify which pages are being used. This helps us analyse data about web page
        traffic and improve our website in order to tailor it to customer needs. We only use this information for
        statistical analysis purposes and then the data is removed from the system.
    </p>
    <p>
        Overall, cookies help us provide you with a better website, by enabling us to monitor which pages you find
        useful and which you do not. A cookie in no way gives us access to your computer or any information about you,
        other than the data you choose to share with us. You can choose to accept or decline cookies.
        Most web browsers automatically accept cookies, but you can usually modify your browser setting
        to decline cookies if you prefer. This may prevent you from taking full advantage of the website.
    </p>
    <h2>Links to other websites</h2>
    <p>
        Our website may contain links to other websites of interest. However, once you have used these links
        to leave our site, you should note that we do not have any control over that other website.
        Therefore, we cannot be responsible for the protection and privacy of any information which you provide whilst
        visiting such sites and such sites are not governed by this privacy statement.
        You should exercise caution and look at the privacy statement applicable to the website in question.
    </p>
    <h2>Controlling your personal information</h2>
    <p>You may choose to restrict the collection or use of your personal information in the following ways:</p>
    <ul>
        <li>
            whenever you are asked to fill in a form on the website, look for the box that you can click to indicate
            that you do not want the information to be used by anybody for direct marketing purposes
        </li>
        <li>
            if you have previously agreed to us using your personal information for direct marketing purposes,
            you may change your mind at any time by letting us know using our Contact Us information
        </li>
    </ul>
    <p>
        We will not sell, distribute or lease your personal information to third parties unless we have your permission
        or are required by law to do so. We may use your personal information to send you promotional information
        about third parties which we think you may find interesting if you tell us that you wish this to happen.
    </p>
    <p>
        You may request details of personal information which we hold about you under the Data Protection Act 1998.
        A small fee will be payable. If you would like a copy of the information held on you please email us this
        request using our Contact Us information.
    </p>
    <p>
        If you believe that any information we are holding on you is incorrect or incomplete,
        please write to or email us as soon as possible, at the above address.
        We will promptly correct any information found to be incorrect.
    </p>
    <h2><a name="list"></a>List of cookies we collect</h2>
    <p>The table below lists the cookies we collect and what information they store.</p>
    <table class="data-table data-table-definition-list">
        <thead>
        <tr>
            <th>Cookie Name</th>
            <th>Cookie Description</th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <th>FORM_KEY</th>
                <td>Stores randomly generated key used to prevent forged requests.</td>
            </tr>
            <tr>
                <th>PHPSESSID</th>
                <td>Your session ID on the server.</td>
            </tr>
            <tr>
                <th>GUEST-VIEW</th>
                <td>Allows guests to view and edit their orders.</td>
            </tr>
            <tr>
                <th>PERSISTENT_SHOPPING_CART</th>
                <td>A link to information about your cart and viewing history, if you have asked for this.</td>
            </tr>
            <tr>
                <th>STF</th>
                <td>Information on products you have emailed to friends.</td>
            </tr>
            <tr>
                <th>STORE</th>
                <td>The store view or language you have selected.</td>
            </tr>
            <tr>
                <th>USER_ALLOWED_SAVE_COOKIE</th>
                <td>Indicates whether a customer allowed to use cookies.</td>
            </tr>
            <tr>
                <th>MAGE-CACHE-SESSID</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>MAGE-CACHE-STORAGE</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>MAGE-CACHE-STORAGE-SECTION-INVALIDATION</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>MAGE-CACHE-TIMEOUT</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>SECTION-DATA-IDS</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>PRIVATE_CONTENT_VERSION</th>
                <td>Facilitates caching of content on the browser to make pages load faster.</td>
            </tr>
            <tr>
                <th>X-MAGENTO-VARY</th>
                <td>Facilitates caching of content on the server to make pages load faster.</td>
            </tr>
            <tr>
                <th>MAGE-TRANSLATION-FILE-VERSION</th>
                <td>Facilitates translation of content to other languages.</td>
            </tr>
            <tr>
                <th>MAGE-TRANSLATION-STORAGE</th>
                <td>Facilitates translation of content to other languages.</td>
            </tr>
        </tbody>
    </table>
</div>
EOD;
        $privacyAndCookiePolicyPage = $this->createPage()->load(
            'privacy-policy-cookie-restriction-mode',
            'identifier'
        );
        $privacyAndCookiePolicyPageId = $privacyAndCookiePolicyPage->getId();
        if ($privacyAndCookiePolicyPageId) {
            $privacyAndCookiePolicyPage->setContent($newPageContent);
            $privacyAndCookiePolicyPage->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            CreateDefaultPages::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Create page instance.
     *
     * @return \Magento\Cms\Model\Page
     */
    private function createPage()
    {
        return $this->pageFactory->create();
    }
}
