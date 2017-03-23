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

    <xsl:param name="referencesFile" select="'references.xml'"/>
    <xsl:variable name="referencesDoc" select="document($referencesFile)"/>
    <xsl:variable name="conflictNames" select="$referencesDoc/list/item[@type = 'conflictNames']"/>

    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="container">
        <xsl:choose>
            <xsl:when test="@name=$conflictNames/@value">
                <xsl:element name="block">
                        <xsl:attribute name="class">
                            <xsl:value-of select="'Magento\Backend\Block\Widget\Grid\Container'"/>
                        </xsl:attribute>
                        <xsl:attribute name="name">
                            <xsl:value-of select="@name"/>
                        </xsl:attribute>
                        <xsl:attribute name="template">
                            <xsl:value-of select="'Magento_Backend::widget/grid/container/empty.phtml'"/>
                        </xsl:attribute>
                        <xsl:apply-templates select="node()|@output"/>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
