<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Fixture\User;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Current password source.
 */
class CurrentPassword implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * User role name.
     *
     * @var string
     */
    protected $data;

    /**
     * @construct
     * @param array $params
     * @param string $data
     */
    public function __construct(array $params, $data = '')
    {
        $this->params = $params;
        /** @var \Magento\Mtf\Config\DataInterface $systemConfig */
        if ($data == '%current_password%') {
            $systemConfig = ObjectManager::getInstance()->create('Magento\Mtf\Config\DataInterface');
            $data = $systemConfig->get('application/0/backendPassword/0/value');
        }
        $this->data = $data;
    }

    /**
     * Persist user role.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
