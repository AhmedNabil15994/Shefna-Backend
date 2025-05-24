<?php

namespace Modules\Catalog\Http\Controllers\FrontEnd;

use Cart;
use Darryldecode\Cart\CartCondition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Area\Entities\State;
use Modules\Catalog\Http\Requests\FrontEnd\CheckoutInformationRequest;
use Modules\Catalog\Repositories\FrontEnd\ProductRepository as Product;
use Modules\Catalog\Traits\ShoppingCartTrait;
use Modules\Company\Entities\DeliveryCharge;
use Modules\Company\Repositories\FrontEnd\CompanyRepository as Company;
use Modules\Core\Traits\CoreTrait;
use Modules\Vendor\Entities\Vendor;
use Modules\Vendor\Entities\VendorDeliveryCharge;
use Modules\Vendor\Repositories\FrontEnd\PaymentRepository as PaymentMethods;
use Modules\Vendor\Repositories\FrontEnd\VendorRepository as VendorRepo;

class CheckoutController extends Controller
{
    use ShoppingCartTrait, CoreTrait;

    protected $product;
    protected $payment;
    protected $company;
    protected $vendor;

    public function __construct(Product $product, PaymentMethods $payment, Company $company, VendorRepo $vendor)
    {
        $this->product = $product;
        $this->payment = $payment;
        $this->company = $company;
        $this->vendor = $vendor;
    }

    public function index(Request $request)
    {
        $paymentMethods = $this->payment->getAll();

        if (config('setting.other.select_shipping_provider') == 'shipping_company') {
            $companyId = config('setting.other.shipping_company') ?? 0;
            $deliveryProvider = $this->company->findById($companyId, ['deliveryTimes']);
        } else {
            $vendorId = getCartContent()->first()->attributes['vendor_id'] ?? null;
            $deliveryProvider = $this->vendor->findById($vendorId, ['deliveryTimes']);
        }

        $deliveryTimes = [];
        $deliveryProviderId = null;
        if ($deliveryProvider && !empty($deliveryProvider->delivery_time_types)) {
            $deliveryTimes = $this->buildDeliveryTimes($deliveryProvider);
            $deliveryProviderId = $deliveryProvider->id;
        }

        return view('catalog::frontend.checkout.index', compact('paymentMethods', 'deliveryTimes', 'deliveryProviderId'));
    }

    public function saveCheckoutInformation(CheckoutInformationRequest $request)
    {
        abort(404);
    }

    public function getContactInfo(Request $request)
    {
        $savedContactInfo = !empty(get_cookie_value(config('core.config.constants.CONTACT_INFO'))) ? (array) \GuzzleHttp\json_decode(get_cookie_value(config('core.config.constants.CONTACT_INFO'))) : [];
        return view('catalog::frontend.checkout.index', compact('savedContactInfo'));
    }

    public function getPaymentMethods(Request $request)
    {
        $cartAttributes = isset(Cart::getConditions()['delivery_fees']) && !empty(Cart::getConditions()['delivery_fees']) ? Cart::getConditions()['delivery_fees']->getAttributes() : null;

        if ($cartAttributes && $cartAttributes['address'] != null) {

            $address = Cart::getCondition('delivery_fees')->getAttributes()['address'];
            $vendor = Vendor::find(Cart::getCondition('vendor')->getType());

            return view('catalog::frontend.checkout.index', compact('address', 'vendor'));
        } else {
            return redirect()->back();
        }
    }

    public function getStateDeliveryPrice(Request $request)
    {
        if (auth()->check()) {
            $userToken = auth()->user()->id ?? null;
        } else {
            $userToken = get_cookie_value(config('core.config.constants.CART_KEY')) ?? null;
        }

        if (is_null($userToken)) {
            return response()->json(["errors" => __('apps::frontend.general.user_token_not_found')], 422);
        }

        if (isset($request->type) && $request->type === 'selected_state') {

            $request->company_id = config('setting.other.shipping_company') ?? 0;
            if (isset($request->state_id) && $request->state_id != 0 && !empty($request->state_id)) {

                if (config('setting.other.select_shipping_provider') == 'shipping_company') {
                    $request->company_id = config('setting.other.shipping_company') ?? 0;
                    $deliveryFeesObject = DeliveryCharge::active()->where('state_id', $request->state_id)->where('company_id', $request->company_id)->first();
                } elseif (config('setting.other.select_shipping_provider') == 'vendor_delivery') {
                    $vendorId = getCartContent()->first()->attributes['vendor_id'] ?? null;
                    $request->request->add(['vendor_id' => $vendorId]);
                    $deliveryFeesObject = VendorDeliveryCharge::active()->where('state_id', $request->state_id)->where('vendor_id', $vendorId)->first();
                } else {
                    $deliveryFeesObject = null;
                }

                $stateObject = State::with('city')->active()->find($request->state_id);

                if ($deliveryFeesObject) {
                    $translatedDeliveryTimeNotes = $deliveryFeesObject->getTranslations('delivery_time');
                    $couponCondition = $this->getConditionByName('coupon_discount');
                    if (!is_null($couponCondition)) {
                        $currentCouponModel = $this->getCurrentCoupon($couponCondition->getAttributes()['coupon']->id);
                        if ($currentCouponModel) {
                            if ($currentCouponModel->free_delivery == 1) {
                                $totalDeliveryPrice = 0;
                                $this->companyDeliveryChargeCondition($request, 0, $userToken, $translatedDeliveryTimeNotes, $stateObject->city_id ?? null, $stateObject->city->country_id ?? null, $deliveryFeesObject->delivery);
                            } else {
                                $this->companyDeliveryChargeCondition($request, $deliveryFeesObject->delivery, $userToken, $translatedDeliveryTimeNotes, $stateObject->city_id ?? null, $stateObject->city->country_id ?? null);
                            }
                        } else {
                            // delete existing coupon
                            $this->removeConditionByName('coupon_discount');
                            $deliveryCondition = $this->getConditionByName('company_delivery_fees');
                            if (!is_null($deliveryCondition)) {
                                $deliveryFees = new CartCondition([
                                    'name' => $this->companyDeliveryCondition,
                                    'type' => $this->companyDeliveryCondition,
                                    'target' => 'total',
                                    'value' => (string) $deliveryFeesObject->delivery,
                                    'attributes' => [
                                        'state_id' => $deliveryCondition->getAttributes()['state_id'],
                                        'city_id' => $this->getCityIdOrCountryId($deliveryCondition->getAttributes()['state_id'], 'city'),
                                        'country_id' => $this->getCityIdOrCountryId($deliveryCondition->getAttributes()['state_id'], 'country'),
                                        'address_id' => $deliveryCondition->getAttributes()['address_id'],
                                        'vendor_id' => $deliveryCondition->getAttributes()['vendor_id'],
                                        'delivery_time_note' => $deliveryCondition->getAttributes()['delivery_time_note'],
                                        'old_value' => $deliveryCondition->getValue(),
                                    ],
                                ]);
                                Cart::session($userToken)->condition([$deliveryFees]);
                            }
                        }

                    } else {
                        $this->companyDeliveryChargeCondition($request, $deliveryFeesObject->delivery, $userToken, $translatedDeliveryTimeNotes, $stateObject->city_id ?? null, $stateObject->city->country_id ?? null);
                    }

                    $condition = Cart::session($userToken)->getCondition('company_delivery_fees');
                    $deliveryPrice = $condition != null ? $condition->getValue() : 0;
                    $data = [
                        'price' => $deliveryFeesObject->delivery,
                        'delivery_time_note' => $deliveryFeesObject->getTranslation('delivery_time', locale()),
                        'totalDeliveryPrice' => isset($totalDeliveryPrice) ? number_format($totalDeliveryPrice, 3) : number_format($deliveryPrice, 3),
                        'total' => number_format(getCartTotal(), 3),
                        'sub_total' => number_format(getCartSubTotal(), 3),
                    ];

                    $couponDiscountCondition = $this->getConditionByName('coupon_discount');
                    if (!is_null($couponDiscountCondition)) {
                        if (!is_null(getCartItemsCouponValue()) && getCartItemsCouponValue() > 0) {
                            $data['coupon_value'] = number_format(getCartItemsCouponValue(), 3);
                        } else {
                            $data['coupon_value'] = number_format($couponDiscountCondition->getValue(), 3);
                        }
                    } else {
                        $data['coupon_value'] = null;
                    }

                    return response()->json(['success' => true, 'data' => $data]);
                } else {
                    if (Cart::session($userToken)->getCondition('company_delivery_fees') != null) {
                        Cart::session($userToken)->removeCartCondition('company_delivery_fees');
                    }
                    $data = [
                        'price' => null,
                        'delivery_time_note' => null,
                        'totalDeliveryPrice' => 0,
                        'total' => number_format(getCartTotal(), 3),
                        'sub_total' => number_format(getCartSubTotal(), 3),
                    ];

                    $couponDiscountCondition = $this->getConditionByName('coupon_discount');
                    if (!is_null($couponDiscountCondition)) {
                        if (!is_null(getCartItemsCouponValue()) && getCartItemsCouponValue() > 0) {
                            $data['coupon_value'] = number_format(getCartItemsCouponValue(), 3);
                        } else {
                            $data['coupon_value'] = number_format($couponDiscountCondition->getValue(), 3);
                        }
                    } else {
                        $data['coupon_value'] = null;
                    }

                    return response()->json(['success' => false, 'data' => $data, 'errors' => __('catalog::frontend.checkout.validation.state_not_supported_by_company')], 422);
                }
            } else {
                return response()->json(['success' => false, 'errors' => __('catalog::frontend.checkout.validation.please_choose_state')], 422);
            }
        } else {
            $data = [
                'price' => null,
                'delivery_time_note' => null,
                'totalDeliveryPrice' => 0,
                'total' => number_format(getCartTotal(), 3),
                'sub_total' => number_format(getCartSubTotal(), 3),
            ];

            $couponDiscountCondition = $this->getConditionByName('coupon_discount');
            if (!is_null($couponDiscountCondition)) {
                if (!is_null(getCartItemsCouponValue()) && getCartItemsCouponValue() > 0) {
                    $data['coupon_value'] = number_format(getCartItemsCouponValue(), 3);
                } else {
                    $data['coupon_value'] = number_format($couponDiscountCondition->getValue(), 3);
                }
            } else {
                $data['coupon_value'] = null;
            }

            return response()->json(['success' => true, 'data' => $data]);
        }
    }
}
