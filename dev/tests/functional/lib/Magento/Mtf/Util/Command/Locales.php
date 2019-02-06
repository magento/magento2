<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

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
    const URL = '/dev/tests/functional/utils/locales.php';

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * Webapi handler.
     *
     * @var WebapiDecorator
     */
    private $webapiHandler;

    /**
     * @param CurlTransport $transport Curl transport protocol
     * @param WebapiDecorator $webapiHandler
     */
    public function __construct(CurlTransport $transport, WebapiDecorator $webapiHandler)
    {
        $this->transport = $transport;
        $this->webapiHandler = $webapiHandler;
    }

    /**
     * Returns array of locales depends on fetching type.
     *
     * @param string $type locales fetching type
     * @return array of locale codes, for example: ['en_US', 'fr_FR']
     */
    public function getList($type = self::TYPE_ALL)
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray($type),
            CurlInterface::POST,
            []
        );
        $result = $this->transport->read();
        $this->transport->close();
        return explode('|', $result);
    }

    /**
     * Prepare parameter array.
     *
     * @param string $type
     * @return array
     */
    private function prepareParamArray($type)
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'type' => urlencode($type)
        ];
    }
}
