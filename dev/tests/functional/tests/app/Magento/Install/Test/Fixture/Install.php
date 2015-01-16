<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Install
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Install extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Install\Test\Repository\Install';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Install\Test\Handler\Install\InstallInterface';

    protected $defaultDataSet = [
    ];

    protected $dbHost = [
        'attribute_code' => 'dbHost',
        'backend_type' => 'virtual',
    ];

    protected $dbUser = [
        'attribute_code' => 'dbUser',
        'backend_type' => 'virtual',
    ];

    protected $dbPassword = [
        'attribute_code' => 'dbPassword',
        'backend_type' => 'virtual',
    ];

    protected $dbName = [
        'attribute_code' => 'dbName',
        'backend_type' => 'virtual',
    ];

    protected $web = [
        'attribute_code' => 'web',
        'backend_type' => 'virtual',
    ];

    protected $admin = [
        'attribute_code' => 'admin',
        'backend_type' => 'virtual',
    ];

    protected $adminUsername = [
        'attribute_code' => 'adminUsername',
        'backend_type' => 'virtual',
    ];

    protected $adminEmail = [
        'attribute_code' => 'adminEmail',
        'backend_type' => 'virtual',
    ];

    protected $adminPassword = [
        'attribute_code' => 'adminPassword',
        'backend_type' => 'virtual',
    ];

    protected $adminConfirm = [
        'attribute_code' => 'adminConfirm',
        'backend_type' => 'virtual',
    ];

    protected $apacheRewrites = [
        'attribute_code' => 'apacheRewrites',
        'backend_type' => 'virtual',
    ];

    protected $dbTablePrefix = [
        'attribute_code' => 'dbTablePrefix',
        'backend_type' => 'virtual',
    ];

    protected $keyOwn = [
        'attribute_code' => 'keyOwn',
        'backend_type' => 'virtual',
    ];

    protected $httpsAdmin = [
        'attribute_code' => 'httpsAdmin',
        'backend_type' => 'virtual',
    ];

    protected $https = [
        'attribute_code' => 'https',
        'backend_type' => 'virtual',
    ];

    protected $httpsFront = [
        'attribute_code' => 'httpsFront',
        'backend_type' => 'virtual',
    ];

    protected $keyValue = [
        'attribute_code' => 'keyValue',
        'backend_type' => 'virtual',
    ];

    protected $language = [
        'attribute_code' => 'language',
        'backend_type' => 'virtual',
    ];

    protected $currency = [
        'attribute_code' => 'language',
        'backend_type' => 'virtual',
    ];

    public function getDbHost()
    {
        return $this->getData('dbHost');
    }

    public function getDbUser()
    {
        return $this->getData('dbUser');
    }

    public function getDbPassword()
    {
        return $this->getData('dbPassword');
    }

    public function getDbName()
    {
        return $this->getData('dbName');
    }

    public function getWeb()
    {
        return $this->getData('web');
    }

    public function getAdmin()
    {
        return $this->getData('admin');
    }

    public function getAdminUsername()
    {
        return $this->getData('adminUsername');
    }

    public function getAdminEmail()
    {
        return $this->getData('adminEmail');
    }

    public function getAdminPassword()
    {
        return $this->getData('adminPassword');
    }

    public function getAdminConfirm()
    {
        return $this->getData('adminConfirm');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getApacheRewrites()
    {
        return $this->getData('apacheRewrites');
    }

    public function getKeyOwn()
    {
        return $this->getData('keyOwn');
    }

    public function getKeyValue()
    {
        return $this->getData('keyValue');
    }

    public function getLanguage()
    {
        return $this->getData('language');
    }

    public function getHttpsAdmin()
    {
        return $this->getData('httpsAdmin');
    }

    public function getHttps()
    {
        return $this->getData('https');
    }

    public function getHttpsFront()
    {
        return $this->getData('httpsFront');
    }

    public function getDbTablePrefix()
    {
        return $this->getData('dbTablePrefix');
    }
}
