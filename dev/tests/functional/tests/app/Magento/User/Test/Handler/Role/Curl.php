<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Handler\Role;

use Magento\Backend\Test\Handler\Extractor;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Creates Admin User role.
 */
class Curl extends AbstractCurl implements RoleInterface
{
    /**
     * Additional mapping values for data.
     *
     * @var array
     */
    protected $additionalMappingData = [];

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     */
    public function __construct(DataInterface $configuration, EventManagerInterface $eventManager)
    {
        $this->mappingData = array_merge(
            (null !== $this->mappingData) ? $this->mappingData : [],
            $this->additionalMappingData
        );
        parent::__construct($configuration, $eventManager);
    }

    /**
     * Curl creation of Admin User Role.
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . 'admin/user_role/saverole/active_tab/info/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Role creating by curl handler was not successful! Response: $response");
        }

        $url = 'admin/user_role/roleGrid/sort/role_id/dir/desc/';
        $regExpPattern = '/col\-role_id[^\>]+\>\s*(\d+)\s*<.td>\s*<[^<>]*?>\s*' . $data['rolename'] . '/siu';

        $extractor = new Extractor($url, $regExpPattern);

        return ['role_id' => $extractor->getData()[1]];
    }

    /**
     * Prepare fixture data before send.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();
        $data = $this->prepareResourceAccess($data);
        $data = array_merge($data, $this->prepareAssignedUsers($fixture));
        $data = array_merge($data, $this->prepareAdminScope($data));

        return $this->replaceMappingData($data);
    }

    /**
     * Prepare role resources data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareResourceAccess(array $data)
    {
        $data['all'] = ($data['resource_access'] == 'All') ? 1 : 0;
        if (isset($data['roles_resources'])) {
            foreach ((array)$data['roles_resources'] as $resource) {
                $data['resource'][] = $resource;
            }
        }

        return $data;
    }

    /**
     * Assign users to the role.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareAssignedUsers(FixtureInterface $fixture)
    {
        if (!$fixture->hasData('in_role_user')) {
            return [];
        }
        $adminUsers = $fixture->getDataFieldConfig('in_role_user')['source']->getAdminUsers();
        $userIds = [];
        foreach ($adminUsers as $adminUser) {
            $userIds[] = $adminUser->getUserId() . "=true";
        }

        return ['in_role_user' => implode('&', $userIds)];
    }

    // TODO: Method should be removed in scope of  MAGETWO-31563

    /**
     * Prepare admin gws option.
     *
     * @param array $data
     * @return array
     */
    protected function prepareAdminScope(array $data)
    {
        if (isset($data['gws_is_all'])) {
            $data['gws_is_all'] = 'All' == $data['gws_is_all'] ? 1 : 0;
        } else {
            $data['gws_is_all'] = 1;
        }

        return $data;
    }
}
