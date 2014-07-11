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

namespace Magento\Customer\Test\Handler\CustomerGroupInjectable;

use Mtf\System\Config;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Handler\Extractor;

/**
 * Class Curl
 * Curl handler for creating customer group
 */
class Curl extends AbstractCurl implements CustomerGroupInjectableInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'customer/group/save/';

    /**
     * POST request for creating Customer Group
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['code'] = $fixture->getCustomerGroupCode();
        $data['tax_class'] = $fixture->getDataFieldConfig('tax_class_id')['source']->getTaxClass()->getId();
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                "Customer Group entity creating by curl handler was not successful! Response: $response"
            );
        }

        return ['customer_group_id' => $this->getCustomerGroupId($data, $response)];
    }

    /**
     * Get id after creating Customer Group
     *
     * @param array $data
     * @return int|null
     */
    public function getCustomerGroupId(array $data)
    {
        $url = 'customer/group/index/sort/time/dir/desc';
        $regExp = '/.*id\/(\d+)\/.*'. $data['code'] .'/siu';
        $extractor = new Extractor($url, $regExp);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }
}
