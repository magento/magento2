<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;

/**
 * Returns array of locales depends on fetching type.
 */
class Locales
{
    /**
     * Type key for fetching all configuration locales.
     */
    const TYPE_ALL = 'all';

    /**
     * Type key for fetching locales that have deployed static content.
     */
    const TYPE_DEPLOYED = 'deployed';

    /**
     * Url to locales.php.
     */
    const URL = 'dev/tests/functional/utils/locales.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * @param CurlTransport $transport Curl transport protocol
     */
    public function __construct(CurlTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Returns array of locales depends on fetching type.
     *
     * @param string $type locales fetching type
     * @return array of locale codes, for example: ['en_US', 'fr_FR']
     */
    public function getList($type = self::TYPE_ALL)
    {
        $url = $_ENV['app_frontend_url'] . self::URL . '?type=' . $type;
        $curl = $this->transport;
        $curl->write($url, [], CurlInterface::GET);
        $result = $curl->read();
        $curl->close();

        return explode('|', $result);
    }
}
