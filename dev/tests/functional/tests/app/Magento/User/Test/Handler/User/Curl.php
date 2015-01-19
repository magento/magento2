<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Handler\User;

use Magento\Backend\Test\Handler\Extractor;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Creates Admin User Entity
 */
class Curl extends AbstractCurl implements UserInterface
{
    /**
     * Curl creation of Admin User
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\User\Test\Fixture\User $fixture */
        $data = $fixture->getData();
        if ($fixture->hasData('role_id')) {
            $data['roles[]'] = $fixture->getDataFieldConfig('role_id')['source']->getRole()->getRoleId();
        }
        $data['is_active'] = (isset($data['is_active']) && ($data['is_active'] === 'Inactive')) ? 0 : 1;
        $url = $_ENV['app_backend_url'] . 'admin/user/save/active_tab/main_section/';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Admin user entity creating by curl handler was not successful! Response: $response");
        }

        $url = 'admin/user/roleGrid/sort/user_id/dir/desc';
        $regExpPattern = '/class=\"\scol\-id col\-user_id\W*>\W+(\d+)\W+<\/td>\W+<td[\w\s\"=\-]*?>\W+?'
            . $data['username'] . '/siu';
        $extractor = new Extractor($url, $regExpPattern);

        return ['user_id' => $extractor->getData()[1]];
    }
}
