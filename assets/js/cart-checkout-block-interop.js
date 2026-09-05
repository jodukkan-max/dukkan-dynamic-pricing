/**
 * Cart & Checkout Block Interop
 */

(function() {

    if (!window.wc || !window.wc.blocksCheckout || !window.wp || !window.wp.data) {
        return;
    }

    const {registerCheckoutFilters} = window.wc.blocksCheckout;

    registerCheckoutFilters('rp-wcdpd-virtual-coupon-labels', {

        coupons: (coupons, context) => {

            // Fetch full cart data
            const cartData = window.wp.data.select('wc/store/cart').getCartData();

            // Map through coupons and apply labels
            return coupons.map((coupon) => {

                // Get custom label
                const custom_label = (
                    cartData &&
                    cartData.extensions &&
                    cartData.extensions.rp_wcdpd_virtual_coupon_data &&
                    cartData.extensions.rp_wcdpd_virtual_coupon_data.labels
                ) ? cartData.extensions.rp_wcdpd_virtual_coupon_data.labels[coupon.code] : null;

                // Check if label exists
                if (custom_label) {
                    var new_coupon = Object.assign({}, coupon);
                    new_coupon.label = custom_label;
                    return new_coupon;
                }

                return coupon;
            });
        }
    });

})();
