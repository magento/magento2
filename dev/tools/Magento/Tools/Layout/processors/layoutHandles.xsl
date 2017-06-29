<!--
/**
 * Copyright © Magento, Inc. All rights reserved.
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
