# Woo Coupon Generator

**Version:** 1.0.0  
**Requires WordPress:** 5.6+  
**Tested up to:** 6.8  
**Requires PHP:** 7.4+  
**Requires Plugins:** WooCommerce, FluentCRM or MailPoet  
**License:** GPL-3.0-or-later  

Generate unique, one-time WooCommerce coupons dynamically inside your email campaigns.  
Supports **FluentCRM Smartcodes** and **MailPoet custom shortcodes**, so each contact or subscriber receives a personalized coupon code.

---

## Features

- **Automatic WooCommerce coupon generation**
  - Creates a new coupon cloned from an existing “template” coupon.
- **Email integration**
  - Works seamlessly inside **FluentCRM** emails and **MailPoet** newsletters.
- **Smartcode & shortcode syntax**
  - FluentCRM: `{{generate_coupon:BASECOUPON}}`
  - MailPoet: `[custom:coupon_BASECOUPON]`
- **Per-contact tracking**
  - Each subscriber receives a unique coupon stored in their profile meta.
- **Reuses existing coupons**
  - If a coupon was already generated for a subscriber, it reuses it instead of creating duplicates.

---

## Usage

### FluentCRM

Insert this Smartcode into any email body:

{{generate_coupon:WELCOME10}}


Where `WELCOME10` is the **base WooCommerce coupon** you’ve already created.

When FluentCRM sends the email:
1. The plugin finds the base coupon.
2. It clones it into a new unique coupon code.
3. Stores that new code under the subscriber’s record.
4. Inserts the code into the outgoing email.

---

### MailPoet

Insert this shortcode into your MailPoet email:

[custom:coupon_WELCOME10]


When MailPoet sends the newsletter:
1. The plugin intercepts the shortcode.
2. Clones the WooCommerce coupon named `WELCOME10`.
3. Generates a unique coupon for each subscriber.
4. Inserts it dynamically.

---

## Installation

1. **Upload the plugin:**
   - Copy the entire `woo-coupon-generator` folder into `/wp-content/plugins/`.
2. **Activate it** via the WordPress **Plugins** screen.
3. Make sure you have **WooCommerce**, **FluentCRM**, and/or **MailPoet** active.
4. Create a base coupon in WooCommerce (e.g., `WELCOME10`).
5. Use the Smartcode or shortcode in your email templates.

---

## License

This plugin is open-source software licensed under the [GPL-3.0-or-later](https://www.gnu.org/licenses/gpl-3.0.html).
