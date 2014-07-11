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

namespace Magento\Catalog\Test\Handler\CatalogAttributeSet;

use Mtf\System\Config;
use Mtf\Fixture\FixtureInterface;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Create new Attribute Set via curl
 */
class Curl extends AbstractCurl implements CatalogAttributeSetInterface
{
    /**
     * Post request for creating Attribute Set
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $fixture->getData();
        if (!isset($data['gotoEdit'])) {
            $data['gotoEdit'] = 1;
        }
        $data['skeleton_set'] = $fixture
            ->getDataFieldConfig('skeleton_set')['source']
            ->getAttributeSet()
            ->getAttributeSetId();

        $url = $_ENV['app_backend_url'] . 'catalog/product_set/save/';
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $data);
        $response = $curl->read();
        $curl->close();

        preg_match(
            '`http.*?id\/(\d*?)\/.*?data-ui-id=\"page-actions-toolbar-delete-button\".*`',
            $response,
            $matches
        );
        $id = isset($matches[1]) ? $matches[1] : null;

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception("Attribute Set creating by curl handler was not successful!");
        }

        return ['attribute_set_id' => $id];
    }
}
