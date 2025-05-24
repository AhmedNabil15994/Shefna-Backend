<?php

namespace Modules\Coupon\Http\Controllers\WebService;

use Carbon\Carbon;
use Cart;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Apps\Http\Controllers\WebService\WebServiceController;
use Modules\Cart\Traits\CartTrait;
use Modules\Catalog\Entities\Product;
use Modules\Coupon\Entities\Coupon;
use Modules\Order\Entities\OrderCoupon;

// use Modules\Coupon\Http\Requests\WebService\CouponRequest;

class CouponController extends WebServiceController
{
    use CartTrait;

    /*
     *** Start - Check Api Coupon
     */
    public function checkCoupon(Request $request)
    {
        if (is_null($request->user_token)) {
            return $this->error(__('apps::frontend.general.user_token_not_found'), [], 422);
        }

        if (getCartContent($request->user_token)->count() == 0) {
            return $this->error(__('coupon::api.coupons.validation.cart_is_empty'), [], 422);
        }

        $coupon = Coupon::where('code', $request->code)->active()->first();
        if ($coupon) {

            if (!is_null($coupon->start_at) && !is_null($coupon->expired_at)) {
                if ($coupon->start_at > Carbon::now()->format('Y-m-d') || $coupon->expired_at < Carbon::now()->format('Y-m-d')) {
                    return $this->error(__('coupon::api.coupons.validation.code.expired'), [], 422);
                }
            }

            if (auth('api')->guest() && !in_array('guest', $coupon->user_type ?? [])) {
                return $this->error(__('coupon::api.coupons.validation.code.custom') . ' guest', [], 422);
            }

            if (auth('api')->check() && !in_array('user', $coupon->user_type ?? [])) {
                return $this->error(__('coupon::api.coupons.validation.code.custom') . ' user', [], 422);
            }

            $coupon_users = $coupon->users->pluck('id')->toArray() ?? [];
            if ($coupon_users != []) {
                if (auth('api')->check() && !in_array(auth('api')->id(), $coupon_users)) {
                    return $this->error(__('coupon::api.coupons.validation.code.custom'), [], 422);
                }

            }

            $couponVendors = $coupon->vendors()->pluck('vendor_id')->toArray();
            array_values($couponVendors);

            $productsVendors = [];
            foreach(getCartContent($request->user_token) as $item){
                $productsVendors[] = $item->attributes->vendor_id;
            }
            array_values($productsVendors);

            for ($i = 0; $i < count($productsVendors); $i++) {
                if(!in_array($productsVendors[$i],$couponVendors)){
                    return $this->error(__('coupon::api.coupons.validation.code.custom'), [], 422);
                }
            }

            if (auth('api')->check()) {
                $userCouponsCount = OrderCoupon::where('coupon_id', $coupon->id)
                    ->whereHas('order', function ($q) {
                        $q->where('user_id', auth('api')->id());
                        $q->whereHas('paymentStatus', function ($q) {
                            $q->whereIn('flag', ['success', 'cash']);
                        });
                    })->count();

                if (!is_null($coupon->user_max_uses) && $userCouponsCount > intval($coupon->user_max_uses)) {
                    return $this->error(__('coupon::api.coupons.validation.user_max_uses'), [], 422);
                }
            }

            // Remove Old General Coupon Condition
            $this->removeCartConditionByType('coupon_discount', $request->user_token);
            $userToken = $request->user_token;

            $cartItems = getCartContent($request->user_token);
            $prdList = $this->getProductsList($coupon, $coupon->flag);
            $prdListIds = array_values(!empty($prdList) ? array_column($prdList->toArray(), 'id') : []);
            $conditionValue = $this->addProductCouponCondition($cartItems, $coupon, $userToken, $prdListIds);
            $data = [
                'discount_value' => $conditionValue > 0 ? number_format($conditionValue, 3) : 0,
                'subTotal' => number_format($this->cartSubTotal($request), 3),
                'total' => number_format($this->cartTotal($request), 3),
            ];

            /*if (!is_null($coupon->flag)) {
            $prdList = $this->getProductsList($coupon, $coupon->flag);
            $prdListIds = array_values(!empty($prdList) ? array_column($prdList->toArray(), 'id') : []);
            $conditionValue = $this->addProductCouponCondition($cartItems, $coupon, $userToken, $prdListIds);
            $data = [
            'discount_value' => $conditionValue > 0 ? number_format($conditionValue, 3) : 0,
            'subTotal' => number_format($this->cartSubTotal($request), 3),
            'total' => number_format($this->cartTotal($request), 3),
            ];
            } else {
            $discount_value = 0;
            if ($coupon->discount_type == "value")
            $discount_value = $coupon->discount_value;
            elseif ($coupon->discount_type == "percentage")
            $discount_value = (getCartSubTotal($userToken) * $coupon->discount_percentage) / 100;

            $this->addProductCouponCondition($cartItems, $coupon, $userToken, []);
            // Apply Coupon Discount Condition On All Products In Cart
            $resultCheck = $this->discountCouponCondition($coupon, $discount_value, $request);
            if (!$resultCheck)
            return $this->error(__('coupon::api.coupons.validation.condition_error'), [], 422);

            $data = [
            'discount_value' => number_format($discount_value, 3),
            'subTotal' => number_format($this->cartSubTotal($request), 3),
            'total' => number_format($this->cartTotal($request), 3),
            ];

            }*/

            return $this->response($data);
        } else {
            return $this->error(__('coupon::api.coupons.validation.code.not_found'), [], 422);
        }
    }

    protected function getProductsList($coupon, $flag = 'products')
    {
        $coupon_vendors = $coupon->vendors ? $coupon->vendors->pluck('id')->toArray() : [];
        $coupon_products = $coupon->products ? $coupon->products->pluck('id')->toArray() : [];
        $coupon_categories = $coupon->categories ? $coupon->categories->pluck('id')->toArray() : [];

        $products = Product::where('status', true);

        if ($flag == 'products') {
            $products = $products->whereIn('id', $coupon_products);
        }

        if ($flag == 'vendors') {
            $products = $products->whereHas('vendor', function ($query) use ($coupon_vendors, $flag) {
                $query->whereIn('id', $coupon_vendors);
                $query->active();
//                $query->whereHas('subscription', function ($q) {
//                    $q->active()->unexpired()->started();
//                });
            });
        }

        if ($flag == 'categories') {
            $products = $products->whereHas('categories', function ($query) use ($coupon_categories) {
                $query->active();
                $query->whereIn('product_categories.category_id', $coupon_categories);
            });
        }

        return $products->get(['id']);
    }

    private function addProductCouponCondition($cartItems, $coupon, $userToken, $prdListIds = [])
    {
        $totalValue = 0;
        $discount_value = 0;

        foreach ($cartItems as $cartItem) {

            if ($cartItem->attributes->product->product_type == 'product') {
                $prdId = $cartKey = $cartItem->id;
            } else {
                $prdId = $cartItem->attributes->product->product->id;
                $cartKey = $cartItem->id;
            }
            // Remove Old Condition On Product
            Cart::session($userToken)->removeItemCondition($cartKey, 'product_coupon');

            if (count($prdListIds) > 0 && in_array($prdId, $prdListIds)) {

                if ($coupon->discount_type == "value") {
                    $discount_value = $coupon->discount_value;
                    $totalValue += intval($cartItem->quantity) * $discount_value;
                } elseif ($coupon->discount_type == "percentage") {
                    $discount_value = (floatval($cartItem->price) * $coupon->discount_percentage) / 100;
                    $totalValue += $discount_value * intval($cartItem->quantity);
                }

                $prdCoupon = new CartCondition(array(
                    'name' => 'product_coupon',
                    'type' => 'product_coupon',
                    'value' => number_format($discount_value * -1, 2),
                ));
                addItemCondition($cartKey, $prdCoupon, $userToken);
                $this->saveEmptyDiscountCouponCondition($coupon, $userToken); // to use it to check coupon in order
            }
        }

        // check free delivery in coupon
        if ($coupon->free_delivery == 1) {
            $deliveryCondition = $this->getConditionByName($userToken, 'company_delivery_fees');
            if (!is_null($deliveryCondition)) {
                $this->addFreeDeliveryChargeCondition($userToken, $deliveryCondition);
            }
        }

        return $totalValue;
    }

    /*
 *** End - Check Api Coupon
 */
}
