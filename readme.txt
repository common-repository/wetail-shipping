=== Wetail Shipping Integration ===
Contributors: Wetail
Tags: Shipping labels, Waybills, Shipping integration, Postnord, DHL, Schenker
License: GPL-3.0
License URI: http://www.opensource.org/licenses/GPL-3.0
Requires at least: 4.0
Tested up to: 6.6.2
Stable tag: 1.0.5
Version: 1.0.5
A quick and effective integration to print shipping labels from WooCommerce order admin. Support for Postnord, DHL, Schenker, Budbee, and Best Transport.


== Description ==
Wetail Shipping makes it easy to connect your WooCommerce shop to the most common carriers on the Scandinavian market. This plugin allows you to book your carrier services for pickup and print shipping labels directly from WooCommerce order admin with ease.

== Supported Carriers ==
* Postnord
* DHL
* Schenker
* Budbee
* Best Transport

== How to get started ==
1. Download and install the plugin on your shop
2. [Set up your account at Wetail](https://wetail.io/integrationer/wetail-shipping/) and receive your license key
3. Add your license key to your WooCommerce installation
4. Map your shop shipping options with Carrier services
5. Get started shipping

== Features ==
* Book carrier pickup from WooCommerce order admin
* Print shipping labels from WooCommerce order admin
* Manage return labels
* Customs declaration documents
* Track & Trace in order emails
* Bulk printing

== Changelog ==
= 1.0.5 =
* Feature: Green check mark in order listing is only visible if label has been printed
* Bugfix: In some themes the print screen was bigger than actual screen. This is corrected.
* Bugfix: Decimals are allowed in settings field for minimum weight
* Bugfix: Space in tracking link in order comments
* Feature: Plugin now has own database table for storing labels. Previously it was stored in order meta
= 1.0.4 =
* Bugfix: WooCommerce Order hook on status change failed, its corrected
* Feature: Product Packing Dimensions loads weight and dimensions at initial load if Product Packing Dimensions entry is not existing for product
= 1.0.3 =
* Bugfix: HPOS support fix
* Bugfix: Link corrections

= 1.0.1 =
* Bugfix: Bulk print
* Bugfix: Order shipping company is used as receiver name instead of Order billing company if receiver is a company

= 1.0 =
* Official version

== Get a Wetail Account ==
Suitable pricing plans for all shop sizes. Start with pay-as-you-go and grow to enterprise plans
[https://wetail.io/integrationer/wetail-shipping/](https://wetail.io/integrationer/wetail-shipping/)

== Installation Docs ==
[https://docs.wetail.io/](https://docs.wetail.io/)

== Usage of data ==
Wetail sends data to the wetail.io domain (in the case of dev mode, wetail.dev is being used). Data will further be processed by the chosen third party shipping provider. Wetail only sends the data that is needed in order to provide the shipping label from each provider.

== Terms of Use and Privacy Policy ==
Terms of Use: [https://wetail.io/terms-of-use-and-privacy-policies/wetail-shipping-terms-of-use/](https://wetail.io/terms-of-use-and-privacy-policies/wetail-shipping-terms-of-use/)
Privacy Policy: [https://wetail.io/terms-of-use-and-privacy-policies/wetail-shipping-privacy-policy/](https://wetail.io/terms-of-use-and-privacy-policies/wetail-shipping-privacy-policy/)
