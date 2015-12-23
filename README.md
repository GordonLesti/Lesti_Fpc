Lesti_Fpc
=========

Branch | Build Status | Coverage
--- | --- | ---
Master | [![Build Status](https://img.shields.io/travis/GordonLesti/Lesti_Fpc/master.svg?style=flat-square)](https://travis-ci.org/GordonLesti/Lesti_Fpc) | [![Coverage Status](https://img.shields.io/coveralls/GordonLesti/Lesti_Fpc/master.svg?style=flat-square)](https://coveralls.io/r/GordonLesti/Lesti_Fpc?branch=master)
Develop | [![Build Status](https://img.shields.io/travis/GordonLesti/Lesti_Fpc/develop.svg?style=flat-square)](https://travis-ci.org/GordonLesti/Lesti_Fpc) | [![Coverage Status](https://img.shields.io/coveralls/GordonLesti/Lesti_Fpc/develop.svg?style=flat-square)](https://coveralls.io/r/GordonLesti/Lesti_Fpc?branch=develop)

## Release Information

*Lesti_Fpc 1.4.4*

## System Requirements

* PHP 5.4 or higher
* Magento CE1.5.x-1.9.x

## Installation

* Install with [modman](https://github.com/colinmollenhour/modman):
    * ```$ modman clone https://github.com/GordonLesti/Lesti_Fpc.git```
* Install with [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer) and add a requirement `gordonlesti/lesti_fpc`

        {
            "require": {
                "gordonlesti/lesti_fpc": "*"
            }
        }

* Install manually:
    * Download latest version [here](https://github.com/GordonLesti/Lesti_Fpc/archive/master.zip)
    * Unzip
    * Copy `app` directory into Magento

## For module creators

You can now make your modules compatible with Lesti_Fpc by injecting configuration to it. There is no longer a need to manually configure handles and parameters in admin.

See this example, configuration goes in your config.xml

```xml
<config>
    <!--  // your modules normal config // -->

    <default>
        <lesti_fpc>
            <cache_actions>
                <cms_index_index />
                <right.reports.product.viewed />
            </cache_actions>
            <miss_uri_params>
                <limit><![CDATA[limit=/^([0-9]+)|(all)$/"]]></limit>
            </miss_uri_params>
        </lesti_fpc>
    </default>
</config>
```

The keys used have the same name as the fields in admin, possible keys are:

* cache_actions
* bypass_handles
* uri_params
* refresh_actions
* miss_uri_params
* dynamic_blocks
* lazy_blocks

If a tag does *not* contain a value (like ```cms_index_index``` above), the tagname is used. If it *does* contain a value, that value is used (like ```limit``` above). 