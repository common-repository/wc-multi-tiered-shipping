<?php

/**
 * @package WCMultiTieredShipping
 */
/*
 * Plugin Name: WC Multi-Tiered Shipping
 * Description: Add a multi-tiered shipping option to the WooCommerce plugin.
 * Extended Description:
 *  This WordPress plugin adds a multi-tiered shipping option to the WooCommerce 
 *  plugin.  Example use: clothing units of merchandise are fairly uniform in size 
 *  such that a predetermined number of units can fit into USPS flat-rate boxes: 
 *  very large, large, medium, and small.
 * Version: 2.1.1
 * Author: M&M Hodges <mhodges2@gmail.com>
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
/*
 * Version 1.0 of this plugin was a derivative work.  Special thanks to:
 *   Joni Halabi, http://www.jhalabi.com, author of the WC Tiered Shipping 
 *   plugin.
 * 
 * The WC Multi-Tiered Shipping v1.0 plugin was written for Emily, co-owner of 
 * ConfiDANCE, by her parents (lucky girl!).  Versions 2.x are dedicated to those
 * that use this plugin and have suggested additional features.
 */

/**
 * Plugin initialization.
 */
function multi_tiered_shipping_init()
{

  if (!class_exists('WC_Multi_Tiered_Shipping'))
  {

    class WC_Multi_Tiered_Shipping extends WC_Shipping_Method
    {

      const CFD_PLUGIN_NAME = 'Multi-Tiered Shipping';
      const CFD_PLUGIN_HANDLE = 'multi_tiered_shipping';
      //
      const CFD_DEFAULT_COUNTRIES_AVAILABILITY = 'all';
      //
      const CFD_DEFAULT_LARGE_ORDER_SHIPPING_COSTS_CALCULATION_METHOD = 'prorate';
      const CFD_DEFAULT_COSTS_LABEL = 'USPS Flat Rate';
      const CFD_DEFAULT_FREE_SHIPPING_LABEL = 'Free Shipping';
      //
      const CFD_DEFAULT_TIER1_QTY = '2';
      const CFD_DEFAULT_TIER2_QTY = '4';
      const CFD_DEFAULT_TIER3_QTY = '10';
      const CFD_DEFAULT_TIER4_QTY = '15';
      const CFD_DEFAULT_IS_TIER3_ENABLED = 'y';
      const CFD_DEFAULT_IS_TIER4_ENABLED = 'y';
      const CFD_DEFAULT_REVIEW_SHIPPING_COSTS_COUNT = 20;
      //
      const CFD_DEFAULT_BASE_COST = '0.00';
      const CFD_DEFAULT_TIER1_AMT = '6.10';
      const CFD_DEFAULT_TIER2_AMT = '11.95';
      const CFD_DEFAULT_TIER3_AMT = '16.35';
      const CFD_DEFAULT_TIER4_AMT = '20.95';
      const CFD_DEFAULT_PRORATED_ITEM_COST = '1.15';

      /**
       * Constructor for the CFD multi-tier shipping class.
       *
       * @since 1.0
       * @access public
       * @return void
       */
      public function __construct()
      {
        $this->id = self::CFD_PLUGIN_HANDLE;
        $this->method_title = __(self::CFD_PLUGIN_NAME);  // Admin settings title
        $this->title = __(self::CFD_PLUGIN_NAME);         // Shipping method list title

        $this->init();
      }

      /**
       * Initialization.
       * 
       * @since 1.0
       * @access public
       * @return void
       */
      function init()
      {
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Save the settings.
        add_action('woocommerce_update_options_shipping_' . $this->id, array(&$this, 'process_admin_options'));
      }

      /**
       * Initialize the Settings Form Fields (overriding default settings API)
       * 
       * @since 1.0
       */
      function init_form_fields()
      {
        global $woocommerce;

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enabled/Disabled', self::CFD_PLUGIN_HANDLE),
                'type' => 'checkbox',
                'label' => 'Enable this shipping method'
            ),
            'availability' => array(
                'title' => __('Availability', 'tiered_shipping'),
                'type' => 'select',
                'class' => 'wc-enhanced-select availability',
                'options' => array(
                    'all' => __('All allowed countries', self::CFD_PLUGIN_HANDLE),
                    'except' => __('All allowed countries, except...', self::CFD_PLUGIN_HANDLE),
                    'specific' => __('Specific countries', self::CFD_PLUGIN_HANDLE)
                ),
                'default' => __(self::CFD_DEFAULT_COUNTRIES_AVAILABILITY, 'tiered_shipping')
            ),
            'countries' => array(
                'title' => __('Countries', self::CFD_PLUGIN_HANDLE),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'options' => $woocommerce->countries->countries,
                'default' => __('', self::CFD_PLUGIN_HANDLE)
            ),
            'cfd_large_order_shipping_costs_calculation_method' => array(
                'title' => __('Large Order Shipping Cost Calculation Method', self::CFD_PLUGIN_HANDLE),
                'type' => 'select',
                'class' => 'wc-enhanced-select availability',
                'options' => array(
                    'prorate' => __('Prorate - prorate the cost of shipping items for orders larger than the last defined Tier.',
                          self::CFD_PLUGIN_HANDLE),
                    'best-fit' => __('Best Fit - calcuate best fit using the defined Tiers (E.G.: items fit in two Tier 4 and one Tier 1 package).',
                          self::CFD_PLUGIN_HANDLE),
                    'free' => __('Free - Free shipping and handling (note: this will cause the Base Cost setting to be ignored).',
                          self::CFD_PLUGIN_HANDLE),
                    'base-cost' => __('Base Cost only - free shipping, but the Base Cost (handling) charges are applicable.',
                          self::CFD_PLUGIN_HANDLE)
                ),
                'description' => __('Additional methods for calculating the costs of large orders are provided for additional flexibility.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => __(self::CFD_DEFAULT_LARGE_ORDER_SHIPPING_COSTS_CALCULATION_METHOD, self::CFD_PLUGIN_HANDLE)
            ),
            'cfd_costs_label' => array(
                'title' => __('Shipping Costs Label', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Shipping method label that is displayed in the shopping cart.', self::CFD_PLUGIN_HANDLE),
                'default' => __(self::CFD_DEFAULT_COSTS_LABEL, self::CFD_PLUGIN_HANDLE)
            ),
            'cfd_free_shipping_label' => array(
                'title' => __('Free Shipping Label', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Free Shipping label that is displayed in the shopping cart when shipping is free.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => __(self::CFD_DEFAULT_FREE_SHIPPING_LABEL, self::CFD_PLUGIN_HANDLE)
            ),
            'cfd_base_cost' => array(
                'title' => __(''
                      . 'Base Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('If needed, you may adjust the base shipping cost upon which the tiered shipping costs are calcuated.<br />TIP: set to $0.00 if there is no base cost.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_BASE_COST
            ),
            'cfd_additional_cost' => array(
                'title' => __('Prorated Item Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('For "Large Orders" (item counts larger than the largest shipping tier), the total cost is the largest shipping tier cost plus the prorated per-item shipping cost for each additional item (plus base cost if applicable).<br />TIP: set to $0.00 if shipping for Large Orders is free; note that this will display the "free shipping" label.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_PRORATED_ITEM_COST
            ),
            'cfd_tier1_qty' => array(
                'title' => __('Tier 1 (Small Qty)', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Maximum number of items for this tier (E.G.: USPS small flat-rate box).<br>Take heed: quantities MUST get progressively larger for predictable results!!!',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER1_QTY
            ),
            'cfd_tier2_qty' => array(
                'title' => __('Tier 2 (Medium Qty)', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Maximum number of items for this tier (E.G.: for USPS medium flat-rate box).',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER2_QTY
            ),
            'cfd_tier3_qty' => array(
                'title' => __('Tier 3 (Large Qty)', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Maximum number of items for this tier (E.G.: for USPS large flat-rate box).',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER3_QTY
            ),
            'cfd_tier4_qty' => array(
                'title' => __('Tier 4 (Largest Qty)', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('Maximum number of items for this tier (E.G.: for USPS largest flat-rate box).',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER4_QTY
            ),
            'cfd_tier1_amt' => array(
                'title' => __('Tier 1 Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('E.G.: cost for USPS small flat-rate box', self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER1_AMT
            ),
            'cfd_tier2_amt' => array(
                'title' => __('Tier 2 Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('E.G.: cost for USPS medium flat-rate box.', self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER2_AMT
            ),
            'cfd_tier3_amt' => array(
                'title' => __('Tier 3 Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('E.G.: cost for USPS large flat-rate box.  Make sure this tier is enabled--see below.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER3_AMT
            ),
            'cfd_tier4_amt' => array(
                'title' => __('Tier 4 Cost', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => __('E.G.: cost for USPS largest flat-rate box.  Make sure this tier is enabled--see below.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_TIER4_AMT
            ),
            'cfd_is_tier3_enabled' => array(
                'title' => __('Is Tier 3 Enabled?', self::CFD_PLUGIN_HANDLE),
                'type' => 'select',
                'description' => __('Enable this tier? It is optional, enabled by default, and is ignored if disabled.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_IS_TIER3_ENABLED,
                'class' => 'wc-enhanced-select',
                'options' => array(
                    'y' => __('Yes, enable this tier', self::CFD_PLUGIN_HANDLE),
                    'n' => __('No, disable this tier', self::CFD_PLUGIN_HANDLE),
                )
            ),
            'cfd_is_tier4_enabled' => array(
                'title' => __('Is Tier 4 Enabled?', self::CFD_PLUGIN_HANDLE),
                'type' => 'select',
                'description' => __('Enable this tier? It is optional, enabled by default, and is ignored if disabled.',
                      self::CFD_PLUGIN_HANDLE),
                'default' => self::CFD_DEFAULT_IS_TIER4_ENABLED,
                'class' => 'wc-enhanced-select',
                'options' => array(
                    'y' => __('Yes, enable this tier', self::CFD_PLUGIN_HANDLE),
                    'n' => __('No, disable this tier', self::CFD_PLUGIN_HANDLE),
                )
            ),
            'cfd_review_shipping_costs_count' => array(
                'title' => __('Review Shipping Costs Max Qty', self::CFD_PLUGIN_HANDLE),
                'type' => 'text',
                'description' => $this->cfd_show_shipping_costs_table(),
                'default' => self::CFD_DEFAULT_REVIEW_SHIPPING_COSTS_COUNT
            ),
        );
      }

      /**
       * Customize the calculation of the shipping cost.
       * 
       * Overrides the WooCommerce default function.
       * 
       * @since 1.0
       * @access public
       * @param mixed $package
       * @return void
       */
      public function calculate_shipping($package = array())
      {
        // Add this method's shipping rate if the user's country is included.
        if (!$this->cfd_is_shipping_destination_allowed($package))
          return;

        // From the cart get the total count of items to be shipped.
        $cfd_total_items = $this->cfd_get_shipping_items_count();

        // Set the shipping label and cost.
        $cfd_shipping = $this->cfd_get_shipping_cost($cfd_total_items);
        $cfd_rate = array(
            'id' => $this->id,
            'label' => $cfd_shipping['label'],
            'cost' => $cfd_shipping['cost']
        );
        $this->add_rate($cfd_rate); // Woocommerce custom rate.
      }

      /**
       * Determine if tiered shipping is allowed.
       * We cannot allow this shipping option if it is not available  
       * in all countries and we do not know what country the user is in.
       *
       * @since 1.0
       * @param array $package
       * @return boolean
       */
      function cfd_is_shipping_destination_allowed($package = array())
      {
        $cfd_countries = $this->get_option('countries');
        $cfd_user_country = $package['destination']['country'];

        $cfd_is_allowed = true;
        switch ($this->get_option('availability', self::CFD_DEFAULT_COUNTRIES_AVAILABILITY))
        {
          case 'all':  // all countries.
            break;
          case 'specific':
            $cfd_is_allowed = false;
            if (in_array($cfd_user_country, $cfd_countries))
              $cfd_is_allowed = true;
            break;
          case 'except':
            if (in_array($cfd_user_country, $cfd_countries))
              $cfd_is_allowed = false;
            break;
        }
        return $cfd_is_allowed;
      }

      /**
       * Return the largest enabled tier.  It is used for calculating
       * costs for orders with item counts larger than the capacity 
       * of the top enabled tier.
       * 
       * @since 2.0
       * @param array $cfd_shipping_tiers
       * @return array
       */
      function cfd_get_largest_enabled_shipping_tier($cfd_shipping_tiers)
      {
        for (end($cfd_shipping_tiers); key($cfd_shipping_tiers) !== null; prev($cfd_shipping_tiers))
        {
          $cfd_current_tier = current($cfd_shipping_tiers);
          if ($cfd_current_tier['enabled'] == 'y')
            return $cfd_current_tier;
        }
      }

      /**
       * Calculate the total number of items to be shipped.  WC specifies that 
       * virtual products should not incur shipping costs since they aren't physical 
       * products.
       * 
       * @since 2.1.1
       * @global type $woocommerce
       * @return type integer
       */
      function cfd_get_shipping_items_count()
      {
        global $woocommerce;

        $cfd_wc_shipment_cnt = 0;
        $cfd_wc_items = $woocommerce->cart->get_cart();

        foreach ($cfd_wc_items as $cfd_wc_item)
        {
          $cfd_wc_product = wc_get_product($cfd_wc_item['product_id']);
          if (!$cfd_wc_product->is_virtual())
          {
            $cfd_wc_shipment_cnt += $cfd_wc_item['quantity'];
          }
        }
        return $cfd_wc_shipment_cnt;
      }

      /**
       * Shipping cost calculations are based on the number of units of merchandise
       * per tier where:       
       *   qty - the maximum quantity of units of merchandise for the tier.
       *   amt - the fixed rate cost for the tier.
       *   tier - the label for this tier; used for displaying tier info.
       *   enabled - the shipping tier is enabled {"y" or "n"}
       * 
       * WARNING: 
       *   Admins must set the enabled tier shipping costs in ascending order by 
       *   quantity, 'qty'.
       * 
       * @since 1.0
       * @return array
       */
      function cfd_get_shipping_tiers()
      {
        return [
            array(
                'qty' => intval($this->get_option('cfd_tier1_qty', self::CFD_DEFAULT_TIER1_QTY)),
                'amt' => $this->get_option('cfd_tier1_amt', self::CFD_DEFAULT_TIER1_AMT),
                'tier' => 'Tier 1',
                'enabled' => 'y', // Always enabled.
            ),
            array(
                'qty' => intval($this->get_option('cfd_tier2_qty', self::CFD_DEFAULT_TIER2_QTY)),
                'amt' => $this->get_option('cfd_tier2_amt', self::CFD_DEFAULT_TIER2_AMT),
                'tier' => 'Tier 2',
                'enabled' => 'y', // Always enabled.
            ),
            array(
                'qty' => intval($this->get_option('cfd_tier3_qty', self::CFD_DEFAULT_TIER3_QTY)),
                'amt' => $this->get_option('cfd_tier3_amt', self::CFD_DEFAULT_TIER3_AMT),
                'tier' => 'Tier 3',
                'enabled' => $this->get_option('cfd_is_tier3_enabled', self::CFD_DEFAULT_IS_TIER3_ENABLED),
            ),
            array(
                'qty' => intval($this->get_option('cfd_tier4_qty', self::CFD_DEFAULT_TIER4_QTY)),
                'amt' => $this->get_option('cfd_tier4_amt', self::CFD_DEFAULT_TIER4_AMT),
                'tier' => 'Tier 4',
                'enabled' => $this->get_option('cfd_is_tier4_enabled', self::CFD_DEFAULT_IS_TIER4_ENABLED),
            ),
        ];
      }

      /**
       * Calculate shipping costs by looking for the appropriate shipping tier 
       * by item quantity.  For Large Orders apply the requested calculation 
       * method.
       * 
       * @since 1.0
       * @param int $cfd_total_items
       * @return array
       */
      function cfd_get_shipping_cost($cfd_total_items)
      {
        // Initializations and defaults.
        $cfd_shipping['order'] = 'small';  // Quantity not larger than largest capacity Tier.
        $cfd_shipping['label'] = $this->get_option('cfd_costs_label', self::CFD_DEFAULT_COSTS_LABEL);
        $cfd_shipping['base'] = floatval($this->get_option('cfd_base_cost', self::CFD_DEFAULT_BASE_COST));

        // Shipping cost, to be calculated.
        $cfd_shipping['shipping'] = 0;

        // Total cost, to be calculated (includes base cost, when applicable).
        $cfd_shipping['cost'] = 0;

        // From the admin settings create an array of shipping tier parameters.
        // This array will be used for subsequent shipping cost calculations.
        $cfd_shipping_tiers = $this->cfd_get_shipping_tiers();

        // Find the appropriate shipping Tier.
        $this->cfd_find_best_fit_shipping_tier($cfd_shipping, $cfd_shipping_tiers, $cfd_total_items);

        if ($cfd_shipping['order'] == 'small')
        {
          // ************************************
          // Calculate Small Order shipping costs
          // ************************************
          $cfd_shipping['cost'] = $this->cfd_util_cost_calc($cfd_shipping['base'], $cfd_shipping['shipping']);
        }
        else
        {
          // ************************************
          // Calculate Large Order shipping costs
          // ************************************
          // Get the specified Large Order Shipping Costs Calculation Method.
          $cfd_shipping['method'] = $this->get_option('cfd_large_order_shipping_costs_calculation_method',
                self::CFD_DEFAULT_LARGE_ORDER_SHIPPING_COSTS_CALCULATION_METHOD);

          // Apply the specified Large Order Shipping Costs Calculation Method.
          switch ($cfd_shipping['method'])
          {
            case 'prorate':
              $this->cfd_get_large_order_prorated_shipping_cost($cfd_shipping, $cfd_shipping_tiers, $cfd_total_items);
              $cfd_shipping['cost'] = $this->cfd_util_cost_calc($cfd_shipping['base'], $cfd_shipping['shipping']);
              break;
            case 'best-fit':
              $this->cfd_get_large_order_best_fit_shipping_cost($cfd_shipping, $cfd_shipping_tiers, $cfd_total_items);
              $cfd_shipping['cost'] = $this->cfd_util_cost_calc($cfd_shipping['base'], $cfd_shipping['shipping']);
              break;
            case 'free':
              $cfd_shipping['label'] = $this->get_option('cfd_free_shipping_label', self::CFD_DEFAULT_FREE_SHIPPING_LABEL);
              $cfd_shipping['cost'] = 0;
              // Base Cost is ignored, as stated.
              break;
            case 'base-cost':
              $cfd_shipping['label'] = $this->get_option('cfd_free_shipping_label', self::CFD_DEFAULT_FREE_SHIPPING_LABEL);
              $cfd_shipping['cost'] = $cfd_shipping['base'];
              break;
          }
        }
        return $cfd_shipping;
      }

      /**
       * Calculate the total shipping cost.  Don't apply the base cost if there is no shipping cost.
       * 
       * @since 2.1
       * @param float $cfd_base_cost
       * @param float $cfd_shipping_cost
       * @return float
       */
      function cfd_util_cost_calc($cfd_base_cost, $cfd_shipping_cost)
      {
        return ($cfd_shipping_cost == 0) ? 0 : $cfd_base_cost + $cfd_shipping_cost;
      }

      /**
       * Calculate the shipping costs for the Large Order Shipping Calculation Method "best-fit".
       * 
       * Each Tier represents progressively larger packaging capacities.  Calculate the minimum
       * quantity of packages required to pack all items.  For example, a larger order may require
       * two Tier 4 packages and one Tier 1 package.  Calculate the shipping costs accordingly.
       * 
       * Calculation strategy: 
       *   1) Determine how many packages of the largest capacity are required to hold as many 
       *      items as possible in the fewest  number of the largest packages.  
       *   2) Determine how many items remain.
       *   3) Determine the best fit package by capacity for the remaining items.
       * 
       * @since 2.1
       * @param array $cfd_shipping
       * @param array $cfd_shipping_tiers
       * @param int $cfd_total_items
       */
      function cfd_get_large_order_best_fit_shipping_cost(&$cfd_shipping, $cfd_shipping_tiers, $cfd_total_items)
      {
        $cfd_last_tier = $this->cfd_get_largest_enabled_shipping_tier($cfd_shipping_tiers);
        $cfd_last_tier_qty = intval($cfd_last_tier['qty']);

        // Calculate how many items will "remain" unpacked using modulo based on the largest capacity package.
        // Basically, how may items won't fit into however many largest capacity packages are needed.
        $cfd_remaining_items = $cfd_total_items % $cfd_last_tier_qty;

        // Determine which Tier's package to use to accommodate the remaining items.
        // This will calculate the shipping cost.
        $this->cfd_find_best_fit_shipping_tier($cfd_shipping, $cfd_shipping_tiers, $cfd_remaining_items);

        // Calculate how many of the largest Tier's packages are required.
        $cfd_last_tier_count = floor($cfd_total_items / $cfd_last_tier_qty);

        // Include the costs for the number of last tier packages also required for sending all items.
        // This will complete the shipping cost calculate (not included any base-cost).
        $cfd_shipping['shipping'] += floatval($cfd_last_tier_count * $cfd_last_tier['amt']);
      }

      /**
       * Calculate the shipping costs for the Large Order Shipping Calculation Method "prorate".
       * If no item costs for proration are provided, assume shipping is free and do not apply the
       * base (handling) cost either.
       * 
       * Prorated shipping costs are calculated by applying the cost of the last defined Tier and
       * adding a prorated amount for each additional item shipped beyond the item capacity of the
       * last defined shipping Tier.  The Base Cost is included in the final calculation.
       * 
       * @since 2.1
       * @param array $cfd_shipping
       * @param array $cfd_shipping_tiers
       * @param int $cfd_total_items
       */
      function cfd_get_large_order_prorated_shipping_cost(&$cfd_shipping, $cfd_shipping_tiers, $cfd_total_items)
      {
        $cfd_prorated_item_cost = floatval($this->get_option('cfd_additional_cost', self::CFD_DEFAULT_PRORATED_ITEM_COST));
        if ($cfd_prorated_item_cost == 0)
        {
          // *********************
          // Legacy Business Logic (required for backwards compatibility)
          // *********************
          // Free shipping for large orders is assumed since no costs for proration of items has been provided (see settings).
          $cfd_shipping['label'] = $this->get_option('cfd_free_shipping_label', self::CFD_DEFAULT_FREE_SHIPPING_LABEL);
        }
        else
        {
          // If the number of items exceeds the capacity of the highest tier, 
          // prorate shipping cost based on the largest tier plus the prorated
          // cost for the extra items.
          $cfd_last_tier = $this->cfd_get_largest_enabled_shipping_tier($cfd_shipping_tiers);
          $cfd_last_tier_cost = floatval($cfd_last_tier['amt']);
          $cfd_last_tier_qty = intval($cfd_last_tier['qty']);
          $cfd_remaining_items = $cfd_total_items - $cfd_last_tier_qty;
          $cfd_shipping['shipping'] = $cfd_last_tier_cost + $cfd_remaining_items * $cfd_prorated_item_cost;
        }
      }

      /**
       * Obtain the shipping info by finding the Tier with the best-fit capacity for the specified 
       * number of items and calculate the shipping cost.  If there are too many items to fit into 
       * any tier, no cost will be calculated.  This is required for backwards compatibility.
       * 
       * @since 2.1
       * @param array $cfd_shipping
       * @param array $cfd_shipping_tiers
       * @param int $cfd_total_items
       */
      function cfd_find_best_fit_shipping_tier(&$cfd_shipping, $cfd_shipping_tiers, $cfd_total_items)
      {
        $cfd_tier_found = false;
        foreach ($cfd_shipping_tiers as $cfd_shipping_tier)
        {
          if ($cfd_shipping_tier['enabled'] == 'y' && $cfd_total_items <= $cfd_shipping_tier['qty'])
          {
            $cfd_shipping['shipping'] = $cfd_shipping_tier['amt'];
            $cfd_shipping['tier'] = $cfd_shipping_tier['tier'];
            $cfd_tier_found = true;
            break;
          }
        }
        if (!$cfd_tier_found)
          $cfd_shipping['order'] = 'large';
      }

      /**
       * Display a cost table to be used for reviewing the cost schedule.
       * 
       * This function is helpful for regression testing to determine if calculations are computed correctly.
       * It is also useful to WP site administrators for ensuring that the shipping parameters provided
       * result in the calculations anticipated.
       * 
       * TODO: investigate display this table via a WC hook so that full table formatting can be utilized.
       *       Currently hampered by WC form sanitizing.
       * 
       * @since 2.0
       * @return string
       */
      function cfd_show_shipping_costs_table()
      {
        $cfd_html = '';
        $pdi_tbl_max = $this->get_option('cfd_review_shipping_costs_count', self::CFD_DEFAULT_REVIEW_SHIPPING_COSTS_COUNT);

        // TODO: add column for tier cost
        $pdi_tbl_head = ' Shipping Calc Method  |  Qty  | Base Cost | Shipping Cost |   Total Cost   | Label ';
        $pdi_tbl_line = ' ----------------------+-------+-----------+---------------+----------------+----------------';
        // Column.......           1                2         3              4               5               6
        // Row Format..  1234567890123456789012 |123456 |1234567890 |12345678901234 |123456789012345 | 
        //   Tiers ....  small order: Tier <N>  | n,nnn |  $ nnn.nn |  $ nnn,nnn.nn | $ n,nnn,nnn.nn | Label
        //   Tier .....  large order: best-fit  | n,nnn |  $ nnn.nn |  $ nnn,nnn.nn | $ n,nnn,nnn.nn | Label       
        //   Prorate...  large order: prorate   | n,nnn |  $ nnn.nn |  $ nnn,nnn.nn | $ n,nnn,nnn.nn | Label
        //   Base Cost.  large order: base-cost | n,nnn |  $ nnn.nn |  $ nnn,nnn.nn | $ n,nnn,nnn.nn | Label  
        //   Free .....  large order: free      | n,nnn |  $ nnn.nn |  $ nnn,nnn.nn | $ n,nnn,nnn.nn | Label
        $fmt[1] = '%22s ';
        $fmt[2] = '|%6s ';
        $fmt[3][0] = '|%10s ';
        $fmt[3][1] = '|%14s '; // HTML character entity compensation
        $fmt[4][0] = '|%14s ';
        $fmt[4][1] = '|%18s '; // HTML character entity compensation
        $fmt[5][0] = '|%15s ';
        $fmt[5][1] = '|%19s '; // HTML character entity compensation
        $fmt[6] = '| %s';

        $cfd_html .= 'After you have saved your new settings, review the table below to determine if the shipping costs are computing as anticipated. This setting does *not* affect computation. It is just for limiting the number of rows in the review table below.';
        $cfd_html .= '<div style = "font-family: monospace;"><br />';

        // The top of the shipping table; table headings.
        $cfd_html .= str_replace(' ', '&nbsp;', $pdi_tbl_head) . '<br />';

        // Loop through the total item quantities, beginning with a single item
        // and calcuate cost and display quantity cost as a row in the shipping table;
        // table detail rows.
        $cfd_previous_desc = '';
        for ($cfd_test_quantity = 1; $cfd_test_quantity <= $pdi_tbl_max; $cfd_test_quantity++)
        {
          // Get the shipping cost for the current quantity.
          $cfd_shipping = $this->cfd_get_shipping_cost($cfd_test_quantity);

          // Formulate a table row description for column 1.
          $cfd_desc = $cfd_shipping['order'] . ' order: ';
          $cfd_desc .= ($cfd_shipping['order'] == 'small') ? $cfd_shipping['tier'] : $cfd_shipping['method'];

          // Each time the tier changes, insert a ruled line.
          if ($cfd_previous_desc !== $cfd_desc)
          {
            $cfd_html .= str_replace(' ', '&nbsp;', $pdi_tbl_line) . '<br />';
            $cfd_previous_desc = $cfd_desc;
          }

          // Insert the costs for the current total quantity.
          $str[1] = $cfd_desc;
          $str[2] = number_format($cfd_test_quantity);
          $str[3] = $this->cfd_util_format_cost($cfd_shipping['base']);
          $str[4] = $this->cfd_util_format_cost($cfd_shipping['shipping']);
          $str[5] = $this->cfd_util_format_cost($cfd_shipping['cost']);
          $str[6] = $cfd_shipping['label'];

          // Where needed, determine format by presence of HTML character entities.
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[1], $str[1]));
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[2], $str[2]));
          $j = (substr($str[3], 0, 1) == '&') ? 1 : 0;
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[3][$j], $str[3]));
          $j = (substr($str[4], 0, 1) == '&') ? 1 : 0;
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[4][$j], $str[4]));
          $j = (substr($str[5], 0, 1) == '&') ? 1 : 0;
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[5][$j], $str[5]));
          $cfd_html .= str_replace(' ', '&nbsp;', sprintf($fmt[6], $str[6]));
          $cfd_html .= '<br />';
        }
        $cfd_html .= '</div>';
        $cfd_html .= '<div><br />If you find this plugin helpful, please throw stars my way!  Thanks.</div>';

        return $cfd_html;
      }

      /**
       * Format the costs using the user set WC currency.
       * 
       * @since 2.1
       * @param string $cfd_cost
       * @return string
       */
      function cfd_util_format_cost($cfd_cost)
      {
        return strip_tags(wc_price(floatval($cfd_cost)));
      }

    }

  }
}

add_action('woocommerce_shipping_init', 'multi_tiered_shipping_init');

function add_multi_tiered_shipping($methods)
{
  $methods[] = 'WC_Multi_Tiered_Shipping';
  return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_multi_tiered_shipping');
