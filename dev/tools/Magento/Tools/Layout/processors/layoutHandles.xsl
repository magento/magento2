<!--
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>
    <!--<xsl:variable name="schemaPath" select="'https://raw.github.com/magento/magento2/master/app/code/Mage/Core/etc/layouts.xsd'"/>-->

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- Update layout (document root) node -->
    <xsl:template match="layout">
        <!--<xsl:copy>-->
            <layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                <xsl:apply-templates select="@*|node()"/>
            </layout>
            <!--<xsl:attribute name="xsi:noNamespaceSchemaLocation">-->
                <!--<xsl:value-of select="$schemaPath"/>-->
            <!--</xsl:attribute>-->
        <!--</xsl:copy>-->
    </xsl:template>

    <xsl:template match="layout/@version"></xsl:template>

    <!-- Update handle node -->
    <xsl:template match="*[name(..)='layout']">
        <xsl:element name="handle">
            <xsl:attribute name="id">
                <xsl:value-of select="name()" />
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:element>
    </xsl:template>

    <!-- Update block node -->
    <xsl:template match="block[@type]">
        <xsl:copy>
            <xsl:attribute name="class">
                <xsl:value-of select="attribute::type" />
            </xsl:attribute>
            <xsl:apply-templates select="node()|@*[name()!='type']"/>
        </xsl:copy>
    </xsl:template>
</xsl:stylesheet>
