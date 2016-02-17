<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Ldap_Node_Schema
 */
#require_once 'Zend/Ldap/Node/Schema.php';
/**
 * @see Zend_Ldap_Node_Schema_AttributeType_OpenLdap
 */
#require_once 'Zend/Ldap/Node/Schema/AttributeType/OpenLdap.php';
/**
 * @see Zend_Ldap_Node_Schema_ObjectClass_OpenLdap
 */
#require_once 'Zend/Ldap/Node/Schema/ObjectClass/OpenLdap.php';

/**
 * Zend_Ldap_Node_Schema_OpenLdap provides a simple data-container for the Schema node of
 * an OpenLDAP server.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Schema
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_Schema_OpenLdap extends Zend_Ldap_Node_Schema
{
    /**
     * The attribute Types
     *
     * @var array
     */
    protected $_attributeTypes = null;
    /**
     * The object classes
     *
     * @var array
     */
    protected $_objectClasses = null;
    /**
     * The LDAP syntaxes
     *
     * @var array
     */
    protected $_ldapSyntaxes = null;
    /**
     * The matching rules
     *
     * @var array
     */
    protected $_matchingRules = null;
    /**
     * The matching rule use
     *
     * @var array
     */
    protected $_matchingRuleUse = null;

    /**
     * Parses the schema
     *
     * @param  Zend_Ldap_Dn $dn
     * @param  Zend_Ldap    $ldap
     * @return Zend_Ldap_Node_Schema Provides a fluent interface
     */
    protected function _parseSchema(Zend_Ldap_Dn $dn, Zend_Ldap $ldap)
    {
        parent::_parseSchema($dn, $ldap);
        $this->_loadAttributeTypes();
        $this->_loadLdapSyntaxes();
        $this->_loadMatchingRules();
        $this->_loadMatchingRuleUse();
        $this->_loadObjectClasses();
        return $this;
    }

    /**
     * Gets the attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return $this->_attributeTypes;
    }

    /**
     * Gets the object classes
     *
     * @return array
     */
    public function getObjectClasses()
    {
        return $this->_objectClasses;
    }

    /**
     * Gets the LDAP syntaxes
     *
     * @return array
     */
    public function getLdapSyntaxes()
    {
        return $this->_ldapSyntaxes;
    }

    /**
     * Gets the matching rules
     *
     * @return array
     */
    public function getMatchingRules()
    {
        return $this->_matchingRules;
    }

    /**
     * Gets the matching rule use
     *
     * @return array
     */
    public function getMatchingRuleUse()
    {
        return $this->_matchingRuleUse;
    }

    /**
     * Loads the attribute Types
     *
     * @return void
     */
    protected function _loadAttributeTypes()
    {
        $this->_attributeTypes = array();
        foreach ($this->getAttribute('attributeTypes') as $value) {
            $val = $this->_parseAttributeType($value);
            $val = new Zend_Ldap_Node_Schema_AttributeType_OpenLdap($val);
            $this->_attributeTypes[$val->getName()] = $val;

        }
        foreach ($this->_attributeTypes as $val) {
            if (count($val->sup) > 0) {
                $this->_resolveInheritance($val, $this->_attributeTypes);
            }
            foreach ($val->aliases as $alias) {
                $this->_attributeTypes[$alias] = $val;
            }
        }
        ksort($this->_attributeTypes, SORT_STRING);
    }

    /**
     * Parses an attributeType value
     *
     * @param  string $value
     * @return array
     */
    protected function _parseAttributeType($value)
    {
        $attributeType = array(
            'oid'                  => null,
            'name'                 => null,
            'desc'                 => null,
            'obsolete'             => false,
            'sup'                  => null,
            'equality'             => null,
            'ordering'             => null,
            'substr'               => null,
            'syntax'               => null,
            'max-length'           => null,
            'single-value'         => false,
            'collective'           => false,
            'no-user-modification' => false,
            'usage'                => 'userApplications',
            '_string'              => $value,
            '_parents'             => array());

        $tokens = $this->_tokenizeString($value);
        $attributeType['oid'] = array_shift($tokens); // first token is the oid
        $this->_parseLdapSchemaSyntax($attributeType, $tokens);

        if (array_key_exists('syntax', $attributeType)) {
            // get max length from syntax
            if (preg_match('/^(.+){(\d+)}$/', $attributeType['syntax'], $matches)) {
                $attributeType['syntax'] = $matches[1];
                $attributeType['max-length'] = $matches[2];
            }
        }

        $this->_ensureNameAttribute($attributeType);

        return $attributeType;
    }

    /**
     * Loads the object classes
     *
     * @return void
     */
    protected function _loadObjectClasses()
    {
        $this->_objectClasses = array();
        foreach ($this->getAttribute('objectClasses') as $value) {
            $val = $this->_parseObjectClass($value);
            $val = new Zend_Ldap_Node_Schema_ObjectClass_OpenLdap($val);
            $this->_objectClasses[$val->getName()] = $val;
        }
        foreach ($this->_objectClasses as $val) {
            if (count($val->sup) > 0) {
                $this->_resolveInheritance($val, $this->_objectClasses);
            }
            foreach ($val->aliases as $alias) {
                $this->_objectClasses[$alias] = $val;
            }
        }
        ksort($this->_objectClasses, SORT_STRING);
    }

    /**
     * Parses an objectClasses value
     *
     * @param string $value
     * @return array
     */
    protected function _parseObjectClass($value)
    {
        $objectClass = array(
            'oid'        => null,
            'name'       => null,
            'desc'       => null,
            'obsolete'   => false,
            'sup'        => array(),
            'abstract'   => false,
            'structural' => false,
            'auxiliary'  => false,
            'must'       => array(),
            'may'        => array(),
            '_string'    => $value,
            '_parents'   => array());

        $tokens = $this->_tokenizeString($value);
        $objectClass['oid'] = array_shift($tokens); // first token is the oid
        $this->_parseLdapSchemaSyntax($objectClass, $tokens);

        $this->_ensureNameAttribute($objectClass);

        return $objectClass;
    }

    /**
     * Resolves inheritance in objectClasses and attributes
     *
     * @param Zend_Ldap_Node_Schema_Item $node
     * @param array                      $repository
     */
    protected function _resolveInheritance(Zend_Ldap_Node_Schema_Item $node, array $repository)
    {
        $data = $node->getData();
        $parents = $data['sup'];
        if ($parents === null || !is_array($parents) || count($parents) < 1) return;
        foreach ($parents as $parent) {
            if (!array_key_exists($parent, $repository)) continue;
            if (!array_key_exists('_parents', $data) || !is_array($data['_parents'])) {
               $data['_parents'] = array();
           }
           $data['_parents'][] = $repository[$parent];
        }
        $node->setData($data);
    }

    /**
     * Loads the LDAP syntaxes
     *
     * @return void
     */
    protected function _loadLdapSyntaxes()
    {
        $this->_ldapSyntaxes = array();
        foreach ($this->getAttribute('ldapSyntaxes') as $value) {
            $val = $this->_parseLdapSyntax($value);
            $this->_ldapSyntaxes[$val['oid']] = $val;
        }
        ksort($this->_ldapSyntaxes, SORT_STRING);
    }

    /**
     * Parses an ldapSyntaxes value
     *
     * @param  string $value
     * @return array
     */
    protected function _parseLdapSyntax($value)
    {
        $ldapSyntax = array(
            'oid'      => null,
            'desc'     => null,
            '_string' => $value);

        $tokens = $this->_tokenizeString($value);
        $ldapSyntax['oid'] = array_shift($tokens); // first token is the oid
        $this->_parseLdapSchemaSyntax($ldapSyntax, $tokens);

        return $ldapSyntax;
    }

    /**
     * Loads the matching rules
     *
     * @return void
     */
    protected function _loadMatchingRules()
    {
        $this->_matchingRules = array();
        foreach ($this->getAttribute('matchingRules') as $value) {
            $val = $this->_parseMatchingRule($value);
            $this->_matchingRules[$val['name']] = $val;
        }
        ksort($this->_matchingRules, SORT_STRING);
    }

    /**
     * Parses an matchingRules value
     *
     * @param  string $value
     * @return array
     */
    protected function _parseMatchingRule($value)
    {
        $matchingRule = array(
            'oid'      => null,
            'name'     => null,
            'desc'     => null,
            'obsolete' => false,
            'syntax'   => null,
            '_string'  => $value);

        $tokens = $this->_tokenizeString($value);
        $matchingRule['oid'] = array_shift($tokens); // first token is the oid
        $this->_parseLdapSchemaSyntax($matchingRule, $tokens);

        $this->_ensureNameAttribute($matchingRule);

        return $matchingRule;
    }

    /**
     * Loads the matching rule use
     *
     * @return void
     */
    protected function _loadMatchingRuleUse()
    {
        $this->_matchingRuleUse = array();
        foreach ($this->getAttribute('matchingRuleUse') as $value) {
            $val = $this->_parseMatchingRuleUse($value);
            $this->_matchingRuleUse[$val['name']] = $val;
        }
        ksort($this->_matchingRuleUse, SORT_STRING);
    }

    /**
     * Parses an matchingRuleUse value
     *
     * @param  string $value
     * @return array
     */
    protected function _parseMatchingRuleUse($value)
    {
        $matchingRuleUse = array(
            'oid'      => null,
            'name'     => null,
            'desc'     => null,
            'obsolete' => false,
            'applies'  => array(),
            '_string'  => $value);

        $tokens = $this->_tokenizeString($value);
        $matchingRuleUse['oid'] = array_shift($tokens); // first token is the oid
        $this->_parseLdapSchemaSyntax($matchingRuleUse, $tokens);

        $this->_ensureNameAttribute($matchingRuleUse);

        return $matchingRuleUse;
    }

    /**
     * Ensures that a name element is present and that it is single-values.
     *
     * @param array $data
     */
    protected function _ensureNameAttribute(array &$data)
    {
        if (!array_key_exists('name', $data) || empty($data['name'])) {
            // force a name
            $data['name'] = $data['oid'];
        }
        if (is_array($data['name'])) {
            // make one name the default and put the other ones into aliases
            $aliases = $data['name'];
            $data['name'] = array_shift($aliases);
            $data['aliases'] = $aliases;
        } else {
            $data['aliases'] = array();
        }
    }

    /**
     * Parse the given tokens into a data structure
     *
     * @param  array $data
     * @param  array $tokens
     * @return void
     */
    protected function _parseLdapSchemaSyntax(array &$data, array $tokens)
    {
        // tokens that have no value associated
        $noValue = array('single-value',
            'obsolete',
            'collective',
            'no-user-modification',
            'abstract',
            'structural',
            'auxiliary');
        // tokens that can have multiple values
        $multiValue = array('must', 'may', 'sup');

        while (count($tokens) > 0) {
            $token = strtolower(array_shift($tokens));
            if (in_array($token, $noValue)) {
                $data[$token] = true; // single value token
            } else {
                $data[$token] = array_shift($tokens);
                // this one follows a string or a list if it is multivalued
                if ($data[$token] == '(') {
                    // this creates the list of values and cycles through the tokens
                    // until the end of the list is reached ')'
                    $data[$token] = array();
                    while ($tmp = array_shift($tokens)) {
                        if ($tmp == ')') break;
                        if ($tmp != '$') {
                            $data[$token][] = Zend_Ldap_Attribute::convertFromLdapValue($tmp);
                        }
                    }
                } else {
                    $data[$token] = Zend_Ldap_Attribute::convertFromLdapValue($data[$token]);
                }
                // create a array if the value should be multivalued but was not
                if (in_array($token, $multiValue) && !is_array($data[$token])) {
                    $data[$token] = array($data[$token]);
                }
            }
        }
    }

    /**
    * Tokenizes the given value into an array
    *
    * @param  string $value
    * @return array tokens
    */
    protected function _tokenizeString($value)
    {
        $tokens = array();
        $matches = array();
        // this one is taken from PEAR::Net_LDAP2
        $pattern = "/\s* (?:([()]) | ([^'\s()]+) | '((?:[^']+|'[^\s)])*)') \s*/x";
        preg_match_all($pattern, $value, $matches);
        $cMatches = count($matches[0]);
        $cPattern = count($matches);
        for ($i = 0; $i < $cMatches; $i++) {     // number of tokens (full pattern match)
            for ($j = 1; $j < $cPattern; $j++) { // each subpattern
                $tok = trim($matches[$j][$i]);
                if (!empty($tok)) {              // pattern match in this subpattern
                    $tokens[$i] = $tok;          // this is the token
                }
            }
        }
        if ($tokens[0] == '(') array_shift($tokens);
        if ($tokens[count($tokens) - 1] == ')') array_pop($tokens);
        return $tokens;
    }
}
