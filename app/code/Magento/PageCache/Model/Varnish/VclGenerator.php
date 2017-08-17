<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\Varnish;

use Magento\PageCache\Model\VclGeneratorInterface;
use Magento\PageCache\Model\VclTemplateLocatorInterface;

/**
 * Class \Magento\PageCache\Model\Varnish\VclGenerator
 *
 */
class VclGenerator implements VclGeneratorInterface
{
    /**
     * @var string|null
     */
    private $backendHost;

    /**
     * @var int|null
     */
    private $backendPort;

    /**
     * @var array
     */
    private $accessList;

    /**
     * @var int|null
     */
    private $gracePeriod;

    /**
     * @var VclTemplateLocatorInterface
     */
    private $vclTemplateLocator;

    /**
     * @var string
     */
    private $sslOffloadedHeader;

    /**
     * @var array
     */
    private $designExceptions;

    /**
     * VclGenerator constructor.
     *
     * @param VclTemplateLocatorInterface $vclTemplateLocator
     * @param string $backendHost
     * @param int $backendPort
     * @param array $accessList
     * @param int $gracePeriod
     * @param string $sslOffloadedHeader
     * @param array $designExceptions
     */
    public function __construct(
        VclTemplateLocatorInterface $vclTemplateLocator,
        $backendHost,
        $backendPort,
        $accessList,
        $gracePeriod,
        $sslOffloadedHeader,
        $designExceptions = []
    ) {
        $this->backendHost = $backendHost;
        $this->backendPort = $backendPort;
        $this->accessList = $accessList;
        $this->gracePeriod = $gracePeriod;
        $this->vclTemplateLocator = $vclTemplateLocator;
        $this->sslOffloadedHeader = $sslOffloadedHeader;
        $this->designExceptions = $designExceptions;
    }

    /**
     * Return generated varnish.vcl configuration file
     *
     * @param int $version
     * @return string
     * @api
     */
    public function generateVcl($version)
    {
        $template = $this->vclTemplateLocator->getTemplate($version);
        return strtr($template, $this->getReplacements());
    }

    /**
     * Prepare data for VCL config
     *
     * @return array
     */
    private function getReplacements()
    {
        return [
            '/* {{ host }} */' => $this->getBackendHost(),
            '/* {{ port }} */' => $this->getBackendPort(),
            '/* {{ ips }} */' => $this->getTransformedAccessList(),
            '/* {{ design_exceptions_code }} */' => $this->getRegexForDesignExceptions(),
            // http headers get transformed by php `X-Forwarded-Proto: https`
            // becomes $SERVER['HTTP_X_FORWARDED_PROTO'] = 'https'
            // Apache and Nginx drop all headers with underlines by default.
            '/* {{ ssl_offloaded_header }} */' => str_replace('_', '-', $this->getSslOffloadedHeader()),
            '/* {{ grace_period }} */' => $this->getGracePeriod(),
        ];
    }

    /**
     * Get regexs for design exceptions
     * Different browser user-agents may use different themes
     * Varnish supports regex with internal modifiers only so
     * we have to convert "/pattern/iU" into "(?Ui)pattern"
     *
     * @return string
     */
    private function getRegexForDesignExceptions()
    {
        $result = '';
        $tpl = "%s (req.http.user-agent ~ \"%s\") {\n"."        hash_data(\"%s\");\n"."    }";

        $expressions = $this->getDesignExceptions();

        if ($expressions) {
            $rules = array_values($expressions);
            foreach ($rules as $i => $rule) {
                if (preg_match('/^[\W]{1}(.*)[\W]{1}(\w+)?$/', $rule['regexp'], $matches)) {
                    if (!empty($matches[2])) {
                        $pattern = sprintf("(?%s)%s", $matches[2], $matches[1]);
                    } else {
                        $pattern = $matches[1];
                    }
                    $if = $i == 0 ? 'if' : ' elsif';
                    $result .= sprintf($tpl, $if, $pattern, $rule['value']);
                }
            }
        }

        return $result;
    }

    /**
     * Get IPs access list that can purge Varnish configuration for config file generation
     * and transform it to appropriate view
     *
     * acl purge{
     *  "127.0.0.1";
     *  "127.0.0.2";
     *
     * @return string
     */
    private function getTransformedAccessList()
    {
        $tpl = "    \"%s\";";
        $result = array_reduce(
            $this->getAccessList(),
            function ($ips, $ip) use ($tpl) {
                return $ips.sprintf($tpl, trim($ip)) . "\n";
            },
            ''
        );
        $result = rtrim($result, "\n");
        return $result;
    }

    /**
     * Get access list
     *
     * @return array
     */
    private function getAccessList()
    {
        return $this->accessList;
    }

    /**
     * Get backend host
     *
     * @return string
     */
    private function getBackendHost()
    {
        return $this->backendHost;
    }

    /**
     * Get backend post
     *
     * @return int
     */
    private function getBackendPort()
    {
        return $this->backendPort;
    }

    /**
     * Get grace period
     *
     * @return int
     */
    private function getGracePeriod()
    {
        return $this->gracePeriod;
    }

    /**
     * Get SSL Offloaded Header
     *
     * @return string
     */
    private function getSslOffloadedHeader()
    {
        return $this->sslOffloadedHeader;
    }

    /**
     * @return array
     */
    private function getDesignExceptions()
    {
        return $this->designExceptions;
    }
}
