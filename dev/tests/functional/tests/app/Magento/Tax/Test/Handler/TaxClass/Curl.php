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

namespace Magento\Tax\Test\Handler\TaxClass;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Class Curl
 * Curl handler for creating customer and product tax class
 */
class Curl extends AbstractCurl implements TaxClassInterface
{
    /**
     * Post request for creating tax class
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();

        $url = $_ENV['app_backend_url'] . 'tax/tax/ajaxSAve/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        $id = $this->getClassId($response);
        return ['id' => $id];
    }

    /**
     * Return saved class id if saved
     *
     * @param $response
     * @return int|null
     * @throws \Exception
     */
    protected function getClassId($response)
    {
        $data = json_decode($response, true);
        if ($data['success'] !== true) {
            throw new \Exception("Tax class creation by curl handler was not successful! Response: $response");
        }
        return isset($data['class_id']) ? (int)$data['class_id'] : null;
    }
}
