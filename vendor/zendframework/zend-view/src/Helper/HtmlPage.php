<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

class HtmlPage extends AbstractHtmlElement
{
    /**
     * Default file type for html
     */
    const TYPE = 'text/html';

    /**
     * Object classid
     */
    const ATTRIB_CLASSID  = 'clsid:25336920-03F9-11CF-8FD0-00AA00686F13';

    /**
     * Default attributes
     *
     * @var array
     */
    protected $attribs = array('classid' => self::ATTRIB_CLASSID);

    /**
     * Output a html object tag
     *
     * @param  string $data    The html url
     * @param  array  $attribs Attribs for the object tag
     * @param  array  $params  Params for in the object tag
     * @param  string $content Alternative content
     * @return string
     */
    public function __invoke($data, array $attribs = array(), array $params = array(), $content = null)
    {
        // Attribs
        $attribs = array_merge($this->attribs, $attribs);

        // Params
        $params = array_merge(array('data' => $data), $params);

        $htmlObject = $this->getView()->plugin('htmlObject');
        return $htmlObject($data, self::TYPE, $attribs, $params, $content);
    }
}
