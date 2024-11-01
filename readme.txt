=== CFD Multi-Tiered Shipping For WooCommerce ===
Contributors: m&mhodges
Tags: woocommerce, shipping, tiered rate
Requires at least: 3.0.1
Tested up to: 4.9.8
Stable tag: 2.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This WordPress plugin adds a multi-tiered flat rate shipping option for the 
WooCommerce plugin.

== Description ==

WordPress administrators can create a multi-tiered flat rate shipping  
cost based on the total number of items in a WooCommerce cart. The  
store administrator can choose to apply this shipping method to all  
available countries or to specific countries.

This flexible shipping method provides up to 4 tiers, suitable for USPS  
flat-rate boxes.  Also provided are several large-order shipping cost 
calculation methods for refining your total shipping costs calculations.

By default there are four tiers; 1, 2, 3, 4 - small to large.  Tiers 3 
and 4 may be disabled if not needed. 

== Examples == 

One of the following examples may help you determine how best to 
configure the settings to get the results you require.  Once you have 
saved your settings, be sure to look at the review table at the bottom 
of the settings page to confirm that your settings are calculating the 
shipping costs as anticipated.

**Example 1**
 
* Default 4-Tier shipping method (all tiers enabled; suitable for USPS flat-rate)
* Base cost: $0.00 (default)
* Large Order Shipping Cost Calculation Method: prorate
<pre>
   Tier|Max Qty| Cost 
   ----+-------+------
     1 |    2  |$ 5.95
     2 |    4  |$12.65
     3 |   10  |$15.90
     4 |   15  |$17.90
    pro|  each |$ 1.12
</pre>

*Results*

* Shipping cost for  5 items = $15.90.
* Shipping cost for 12 items = $17.90.
* Shipping cost for 20 items = $17.90 + 5 x $1.12.  Total cost is $23.50.

**Example 2**

* Custom 3-Tier shipping method (tier 4 disabled)
* Base cost: $0.00 (default)
* Large Order Shipping Cost Calculation Method: prorate
<pre>
  Tier|Max Qty| Cost
  ----+-------+------
    1 |   10  |$ 7.00
    2 |   40  |$21.00
    3 |  160  |$42.00
    4 |   disabled
   pro|  each |$ 2.00</code>
</pre>

*Results*

* Shipping cost for  35 items = $21.00.
* Shipping cost for 120 items = $42.00.
* Prorated shipping cost for 200 items = $42.00 + 40 x $2.00.  Total cost is $122.00.

*Note*

* Disabling tier 3 and enabling tier 4 works results in exactly the same cost calculations.

**Example 3**

* Default 4-Tier shipping method with free shipping for large orders.
* Base cost: $2.00
* Large Order Shipping Cost Calculation Method: prorate
<pre>
  Tier|Max Qty| Cost
  ----+-------+------
    1 |   15  |$ 5.00
    2 |   30  |$10.00
    3 |   45  |$15.00
    4 |   60  |$20.00
   pro|  each |$ 0.00  <-- Large orders are free!!!
</pre>

*Results*

* Shipping cost for 10 items = $ 5.00 + $2.00.  Total cost is $ 7.00 including $2.00 base cost.
* Shipping cost for 50 items = $20.00 + $2.00.  Total cost is $22.00 including $2.00 base cost.
* Shipping is free for orders greater than 60 items.

**Example 4**

* Custom 2-Tier shipping method with free shipping (tiers 3 and 4 disabled) for large orders.
* Base cost: $3.50
* Large Order Shipping Cost Calculation Method: prorate
<pre>
  Tier|Max Qty| Cost
  ----+-------+------
    1 |   15  |$ 6.00
    2 |   30  |$10.00
    3 | disabled
    4 | disabled
   pro|  each |$ 0.00  <-- Large orders are free!!!
</pre>

*Results*

* Shipping cost for 16 items = $10.00 + $3.50.  Total cost is $13.50 including $3.50 base cost.
* Shipping is free for orders greater than 30 items.

== Shipping Costs Preview ==

After updating and saving the settings, be sure to review the shipping 
costs calculations table provided at the bottom of the settings page.  
This will help you ensure that your settings achieve the desired 
calculations.

== Installation ==

1. Upload the plugin to your WordPress installation and activate the plugin.
2. Go to the "Shipping" tab on the WooCommerce Settings page in the WP Admin. 
3. In the list of submenus that begins with Shipping Zones, click on  
   "Multi-Tiered Shipping" to enable this method and update its settings.

== Updating Version 1.0 to Version 2.x ==

For those that are updating from version 1.0, there are new settings.  The
defaults ensure that there is no change to the shipping cost calculations 
that you have already set up.

== Changelog ==

= 2.1.1 =
* FIX: WooCommerce specifies that Virtual products should not be subjected to shipping charges.  This fix ensures that the plugin follows this convention.
* Confirm compatibility with current WordPress and WooCommerce releases.

= 2.1.0 =
* NEW: Implement a Large Orders Shipping Calculation Method setting (by community request).  Support a "best-fit" method, in addition to the methods for prorated and free shipping.
* Update the preview shipping costs table to reflect the additional flexibility for large orders.  And improve table formatting.
* Confirm compatibility with current WordPress and WooCommerce releases.

= 2.0.1 =
* FIX: Preview shipping costs table: replace money_format() with wp_price().  The money_format function does not work on all systems.
* Enhancement: The preview shipping costs table shows the costs in the currency specified by the WooCommerce settings.
* Confirm compatibility with current WordPress and WooCommerce releases.

= 2.0.0 =
* NEW: Flexible number of tiers (by community request): the number of tiers to utilize is more flexible; tiers 3 and/or 4 can be enabled/disabled. 
* NEW: Free shipping (by community request): quantity-based free shipping is supported by setting the prorated per-item shipping cost to $0.00 so that sufficiently large orders allow for free shipping.
* NEW: Base cost: base cost was implicitly $0.00 and can now be set to a preferred value.
* NEW: Enhanced country support (inspired community request for States-based selection): this shipping method may be applied to all or just select countries.  Country exclusion also supported. 
* NEW: Preview shipping costs table: the bottom of the settings page provides a preview table so that the calculations can be reviewed.
* Update and greatly expand the readme by adding a variety of detailed shipping cost settings examples.
* Update and expand in-line source code documentation: the code is structured to help others fork and modify this plugin more easily.
* Update the default shipping cost for each tier to more closely reflect current USPS rates.
* New and updated screen shots are provided for the default shipping labels.
* Consistently use the word "cost" throughout rather than also referencing "fee".
* Ensure backwards compatibility with 1.0.0 given that there are new options to set.
* Confirm compatibility with current WordPress and WooCommerce releases.

= 1.0.0 =
* Initial release.
* Feature: provide 4 tiers for calculating quantity-based shipping fees.
* Feature: prorate costs for quantities that exceed the 4 shipping tiers.