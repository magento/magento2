<!--
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- Update block node -->
    <xsl:template match="block">
        <xsl:copy>
            <xsl:attribute name="class">
                <xsl:value-of select="attribute::type"/>
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*[name()!='type']"/>
        </xsl:copy>
    </xsl:template>

    <!-- Update action node -->
    <xsl:template match="*[name()='action']">
        <xsl:copy>
            <xsl:apply-templates select="@*" />
            <xsl:call-template name="argument"/>
        </xsl:copy>
    </xsl:template>

    <!-- Update block arguments -->
    <xsl:template match="arguments">
        <xsl:element name="arguments">
            <xsl:call-template name="argument"/>
        </xsl:element>
    </xsl:template>

    <xsl:template name="argument">
        <xsl:for-each select="child::*">
            <xsl:element name="argument">
                <xsl:attribute name="name">
                    <xsl:value-of select="name()" />
                </xsl:attribute>
                <xsl:call-template name="type"/>
            </xsl:element>
        </xsl:for-each>
    </xsl:template>

    <!-- Add type attribute -->
    <xsl:template name="type">
        <xsl:choose>
            <xsl:when test="@type='url'">
                <!-- Type Url-->
                <xsl:call-template name="url"/>
            </xsl:when>
            <xsl:when test="@type = 'options'">
                <!-- Type options-->
                <xsl:call-template name="options"/>
            </xsl:when>
            <xsl:when test="count(child::*) &gt; 0">
                <!-- Type Array-->
                <xsl:call-template name="array"/>
            </xsl:when>
            <xsl:otherwise>
                <!-- Scalar types -->
                <xsl:attribute name="xsi:type">
                    <xsl:choose>
                        <!--<xsl:when test="count(child::*) &gt; 0">array</xsl:when>-->
                        <xsl:when test="php:function('preg_match', '/^(Mage)_((?!::).)*$/', string(.))=1">object</xsl:when>
                        <!--<xsl:when test="php:function('preg_match', '/(true|false)/', string(.))=1">boolean</xsl:when>-->
                        <!--<xsl:when test="php:function('preg_match', '/^[0-9\.\,\+\-]+$/', string(.))=1">number</xsl:when>-->
                        <!--<xsl:when test="@type='url'">url</xsl:when>-->
                        <xsl:otherwise>string</xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:apply-templates select="node()|@*[name()!='type' and name()!='helper']"/>
                <!--<xsl:call-template name="helper"/>-->
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="url">
        <xsl:attribute name="xsi:type">url</xsl:attribute>
        <xsl:attribute name="path">
            <xsl:value-of select="child::path"/>
        </xsl:attribute>
        <xsl:for-each select="child::params/*">
            <xsl:element name="param">
                <xsl:attribute name="name">
                    <xsl:value-of select="name()"/>
                </xsl:attribute>
                <xsl:apply-templates select="node()|@*"/>
            </xsl:element>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="array">
        <xsl:attribute name="xsi:type">array</xsl:attribute>
        <!--<xsl:call-template name="helper"/>-->
        <xsl:for-each select="child::*">
            <xsl:choose>
                <xsl:when test="name()='updater'">
                    <!--<xsl:call-template name="updater"/>-->
                    <xsl:copy>
                        <xsl:apply-templates select="node()|@*"/>
                    </xsl:copy>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:element name="item">
                        <xsl:attribute name="name">
                            <xsl:value-of select="name()" />
                        </xsl:attribute>
                        <xsl:call-template name="type"/>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="options">
        <xsl:attribute name="xsi:type">options</xsl:attribute>
        <xsl:choose>
            <xsl:when test="count(child::*)=0">
                <xsl:attribute name="model">
                    <xsl:value-of select="."/>
                </xsl:attribute>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="child::*">
                    <xsl:element name="item">
                        <xsl:attribute name="name">
                            <xsl:value-of select="name()" />
                        </xsl:attribute>
                        <xsl:call-template name="type"/>
                    </xsl:element>
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
