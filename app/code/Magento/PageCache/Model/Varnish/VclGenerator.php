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
 * @since 2.2.0
 */
class VclGenerator implements VclGeneratorInterface
{
    /**
     * @var string|null
     * @since 2.2.0
     */
    private $backendHost;

    /**
     * @var int|null
     * @since 2.2.0
     */
    private $backendPort;

    /**
     * @var array
     * @since 2.2.0
     */
    private $accessList;

    /**
     * @var int|null
     * @since 2.2.0
     */
    private $gracePeriod;

    /**
     * @var VclTemplateLocatorInterface
     * @since 2.2.0
     */
    private $vclTemplateLocator;

    /**
     * @var string
     * @since 2.2.0
     */
    private $sslOffloadedHeader;

    /**
     * @var array
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function getAccessList()
    {
        return $this->accessList;
    }

    /**
     * Get backend host
     *
     * @return string
     * @since 2.2.0
     */
    private function getBackendHost()
    {
        return $this->backendHost;
    }

    /**
     * Get backend post
     *
     * @return int
     * @since 2.2.0
     */
    private function getBackendPort()
    {
        return $this->backendPort;
    }

    /**
     * Get grace period
     *
     * @return int
     * @since 2.2.0
     */
    private function getGracePeriod()
    {
        return $this->gracePeriod;
    }

    /**
     * Get SSL Offloaded Header
     *
     * @return string
     * @since 2.2.0
     */
    private function getSslOffloadedHeader()
    {
        return $this->sslOffloadedHeader;
    }

    /**
     * @return array
     * @since 2.2.0
     */
    private function getDesignExceptions()
    {
        return $this->designExceptions;
    }
}
