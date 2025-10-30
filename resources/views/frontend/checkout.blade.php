@extends('frontend.layouts.app')

@section('content')
<section class="my-4 gry-bg">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-lg-8 mx-auto">
                <form class="form-default" data-toggle="validator"
                      action="{{ route('payment.checkout') }}" role="form" method="POST" id="checkout-form">
                    @csrf

                    <div class="accordion" id="accordioncCheckoutInfo">

                        <!-- SHIPPING INFO -->
                        <div class="card rounded-0 border shadow-none" style="margin-bottom: 2rem;">
                            <div class="card-header border-bottom-0 py-3 py-xl-4" id="headingShippingInfo"
                                 type="button" data-toggle="collapse" data-target="#collapseShippingInfo"
                                 aria-expanded="true" aria-controls="collapseShippingInfo">
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                        <path id="shipping_check" d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z"
                                              transform="translate(-48 -48)" fill="#9d9da6"/>
                                    </svg>
                                    <span class="ml-2 fs-19 fw-700">{{ translate('Shipping Info') }}</span>
                                </div>
                                <i class="las la-angle-down fs-18"></i>
                            </div>
                            <div id="collapseShippingInfo" class="collapse show"
                                 aria-labelledby="headingShippingInfo" data-parent="#accordioncCheckoutInfo">
                                <div class="card-body" id="shipping_info">
                                    @include('frontend.partials.cart.shipping_info', ['address_id' => $address_id])
                                </div>
                            </div>
                        </div>

                        <!-- DELIVERY INFO -->
                        <div class="card rounded-0 border shadow-none" style="margin-bottom: 2rem; overflow: visible !important;">
                            <div class="card-header border-bottom-0 py-3 py-xl-4" id="headingDeliveryInfo"
                                 type="button" data-toggle="collapse" data-target="#collapseDeliveryInfo"
                                 aria-expanded="true" aria-controls="collapseDeliveryInfo">
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                        <path id="delivery_check" d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z"
                                              transform="translate(-48 -48)" fill="#9d9da6"/>
                                    </svg>
                                    <span class="ml-2 fs-19 fw-700">{{ translate('Delivery Info') }}</span>
                                </div>
                                <i class="las la-angle-down fs-18"></i>
                            </div>
                            <div id="collapseDeliveryInfo" class="collapse show"
                                 aria-labelledby="headingDeliveryInfo" data-parent="#accordioncCheckoutInfo">
                                <div class="card-body" id="delivery_info">
                                    @php
                                        $admin_products = $seller_products = $admin_product_variation = $seller_product_variation = [];
                                        $isRenewPackageAdmin = $isRenewPackage = "false";
                                        $pickup_point_list = get_setting('pickup_point') == 1 ? get_all_pickup_points() : [];
                                        foreach ($carts as $cartItem){
                                            $product = get_single_product($cartItem['product_id']);
                                            if($product->added_by == 'admin'){
                                                $admin_products[] = $cartItem['product_id'];
                                                $admin_product_variation[] = $cartItem['variation'];
                                            }else{
                                                $seller_products[$product->user_id][] = $cartItem['product_id'];
                                                $seller_product_variation[] = $cartItem['variation'];
                                            }
                                        }
                                    @endphp

                                    <!-- ADMIN PRODUCTS -->
                                    @if (!empty($admin_products))
                                        <div class="card mb-4 border-0 rounded-0 shadow-none">
                                            <div class="card-header py-3 px-0 border-bottom-0">
                                                <h5 class="fs-16 fw-700 text-dark mb-0">
                                                    {{ get_setting('site_name') }} {{ translate('Inhouse Products') }}
                                                </h5>
                                            </div>
                                            <div class="card-body p-0">
                                                <ul class="list-group list-group-flush border p-3 mb-3">
                                                    @php $physical = false; @endphp
                                                    @foreach ($admin_products as $idx => $cartItemId)
                                                        @php
                                                            $product = get_single_product($cartItemId);
                                                            if($product->digital == 0) $physical = true;
                                                            if(stripos($product->getTranslation('name'), 'RENEW') !== false){
                                                                $isRenewPackageAdmin = "true"; $physical = false;
                                                            }
                                                        @endphp
                                                        <li class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <span class="mr-2 mr-md-3">
                                                                    <img src="{{ $product->thumbnail ? my_asset($product->thumbnail->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                                         class="img-fit size-60px"
                                                                         alt="{{ $product->getTranslation('name') }}"
                                                                         onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                                </span>
                                                                <span class="fs-14 fw-400 text-dark">
                                                                    {{ $product->getTranslation('name') }}
                                                                    @if(!empty($admin_product_variation[$idx]))
                                                                        <br><span class="fs-12 text-secondary">
                                                                            {{ translate('Variation') }}: {{ $admin_product_variation[$idx] }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                @if($isRenewPackageAdmin == "true")
                                                    <div class="row border-top pt-3">
                                                        <div class="col-md-6"><h6 class="fs-15 fw-600">{{ translate('Enter Smart Card Number') }}</h6></div>
                                                        <div class="col-md-6">
                                                            <input type="text" name="card_number_admin" class="form-control form-control-lg renew-card" required>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($physical)
                                                    <div class="row pt-3">
                                                        <div class="col-md-6"><h6 class="fs-14 fw-700 mt-3">{{ translate('Choose Delivery Type') }}</h6></div>
                                                        <div class="col-md-6">
                                                            <div class="row gutters-5">
                                                                @if(get_setting('shipping_type') != 'carrier_wise_shipping')
                                                                    <div class="col-6">
                                                                        <label class="aiz-megabox d-block bg-white mb-0">
                                                                            <input type="radio" name="shipping_type_admin" value="home_delivery"
                                                                                   onchange="show_pickup_point(this,'admin')" data-target=".pickup_point_id_admin" checked>
                                                                            <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ translate('Home Delivery') }}</span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                @else
                                                                    <div class="col-6">
                                                                        <label class="aiz-megabox d-block bg-white mb-0">
                                                                            <input type="radio" name="shipping_type_admin" value="carrier"
                                                                                   onchange="show_pickup_point(this,'admin')" data-target=".pickup_point_id_admin" checked>
                                                                            <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ translate('Carrier') }}</span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                @endif

                                                                @if($pickup_point_list)
                                                                    <div class="col-6">
                                                                        <label class="aiz-megabox d-block bg-white mb-0">
                                                                            <input type="radio" name="shipping_type_admin" value="pickup_point"
                                                                                   onchange="show_pickup_point(this,'admin')" data-target=".pickup_point_id_admin">
                                                                            <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ translate('Local Pickup') }}</span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            @if($pickup_point_list)
                                                                <div class="mt-3 pickup_point_id_admin d-none">
                                                                    <select class="form-control aiz-selectpicker rounded-0"
                                                                            name="pickup_point_id_admin" data-live-search="true">
                                                                        <option>{{ translate('Select your nearest pickup point') }}</option>
                                                                        @foreach($pickup_point_list as $pp)
                                                                            <option value="{{ $pp->id }}"
                                                                                    data-content="<span class='d-block'>
                                                                                                    <span class='d-block fs-16 fw-600 mb-2'>{{ $pp->getTranslation('name') }}</span>
                                                                                                    <span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pp->getTranslation('address') }}</span>
                                                                                                    <span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pp->phone }}</span>
                                                                                                  </span>">
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if(get_setting('shipping_type') == 'carrier_wise_shipping')
                                                        <div class="row pt-3 carrier_id_admin">
                                                            @foreach($carrier_list as $i => $carrier)
                                                                <div class="col-md-12 mb-2">
                                                                    <label class="aiz-megabox d-block bg-white mb-0">
                                                                        <input type="radio" name="carrier_id_admin"
                                                                               value="{{ $carrier->id }}" {{ $i==0?'checked':'' }}>
                                                                        <span class="d-flex p-3 aiz-megabox-elem rounded-0">
                                                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                            <span class="flex-grow-1 pl-3 fw-600">
                                                                                <img src="{{ uploaded_asset($carrier->logo) }}" class="w-50px img-fit">
                                                                            </span>
                                                                            <span class="flex-grow-1 pl-3 fw-600">{{ $carrier->name }}</span>
                                                                            <span class="flex-grow-1 pl-3 fw-600">{{ translate('Transit in').' '.$carrier->transit_time }}</span>
                                                                            <span class="flex-grow-1 pl-3 fw-600">{{ single_price(carrier_base_price($carts,$carrier->id,get_admin()->id)) }}</span>
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- SELLER PRODUCTS -->
                                    @if (!empty($seller_products))
                                        @foreach ($seller_products as $sellerId => $sellerProdIds)
                                            @php $physical = false; @endphp
                                            <div class="card mb-4 border-0 rounded-0 shadow-none">
                                                <div class="card-header py-3 px-0 border-bottom-0">
                                                    <h5 class="fs-16 fw-700 text-dark mb-0">
                                                        {{ get_shop_by_user_id($sellerId)->name }} {{ translate('Products') }}
                                                    </h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <ul class="list-group list-group-flush border p-3 mb-3">
                                                        @foreach ($sellerProdIds as $idx => $cartItemId)
                                                            @php
                                                                $product = get_single_product($cartItemId);
                                                                if($product->digital == 0) $physical = true;
                                                                if(stripos($product->getTranslation('name'), 'RENEW') !== false){
                                                                    $isRenewPackage = "true"; $physical = false;
                                                                }
                                                            @endphp
                                                            <li class="list-group-item">
                                                                <div class="d-flex align-items-center">
                                                                    <span class="mr-2 mr-md-3">
                                                                        <img src="{{ $product->thumbnail ? my_asset($product->thumbnail->file_name) : static_asset('assets/img/placeholder.jpg') }}"
                                                                             class="img-fit size-60px"
                                                                             alt="{{ $product->getTranslation('name') }}"
                                                                             onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                                                    </span>
                                                                    <span class="fs-14 fw-400 text-dark">
                                                                        {{ $product->getTranslation('name') }}
                                                                        @if(!empty($seller_product_variation[$idx]))
                                                                            <br><span class="fs-12 text-secondary">
                                                                                {{ translate('Variation') }}: {{ $seller_product_variation[$idx] }}
                                                                            </span>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                    @if($isRenewPackage == "true")
                                                        <div class="row border-top pt-3">
                                                            <div class="col-md-6"><h6 class="fs-15 fw-600">{{ translate('Enter Smart Card Number') }}</h6></div>
                                                            <div class="col-md-6">
                                                                <input type="text" name="card_number_{{ $sellerId }}"
                                                                       class="form-control form-control-lg renew-card" required>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($physical)
                                                        <div class="row pt-3">
                                                            <div class="col-md-6"><h6 class="fs-14 fw-700 mt-3">{{ translate('Choose Delivery Type') }}</h6></div>
                                                            <div class="col-md-6">
                                                                <div class="row gutters-5">
                                                                    @if(get_setting('shipping_type') != 'carrier_wise_shipping')
                                                                        <div class="col-6">
                                                                            <label class="aiz-megabox d-block bg-white mb-0">
                                                                                <input type="radio" name="shipping_type_{{ $sellerId }}" value="home_delivery"
                                                                                       onchange="show_pickup_point(this,{{ $sellerId }})" data-target=".pickup_point_id_{{ $sellerId }}" checked>
                                                                                <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                    <span class="flex-grow-1 pl-3 fw-600">{{ translate('Home Delivery') }}</span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    @else
                                                                        <div class="col-6">
                                                                            <label class="aiz-megabox d-block bg-white mb-0">
                                                                                <input type="radio" name="shipping_type_{{ $sellerId }}" value="carrier"
                                                                                       onchange="show_pickup_point(this,{{ $sellerId }})" data-target=".pickup_point_id_{{ $sellerId }}" checked>
                                                                                <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                    <span class="flex-grow-1 pl-3 fw-600">{{ translate('Carrier') }}</span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    @endif

                                                                    @if($pickup_point_list)
                                                                        <div class="col-6">
                                                                            <label class="aiz-megabox d-block bg-white mb-0">
                                                                                <input type="radio" name="shipping_type_{{ $sellerId }}" value="pickup_point"
                                                                                       onchange="show_pickup_point(this,{{ $sellerId }})" data-target=".pickup_point_id_{{ $sellerId }}">
                                                                                <span class="d-flex aiz-megabox-elem rounded-0" style="padding:0.75rem 1.2rem;">
                                                                                    <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                    <span class="flex-grow-1 pl-3 fw-600">{{ translate('Local Pickup') }}</span>
                                                                                </span>
                                                                            </label>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                @if($pickup_point_list)
                                                                    <div class="mt-4 pickup_point_id_{{ $sellerId }} d-none">
                                                                        <select class="form-control aiz-selectpicker rounded-0"
                                                                                name="pickup_point_id_{{ $sellerId }}" data-live-search="true">
                                                                            <option>{{ translate('Select your nearest pickup point') }}</option>
                                                                            @foreach($pickup_point_list as $pp)
                                                                                <option value="{{ $pp->id }}"
                                                                                        data-content="<span class='d-block'>
                                                                                                        <span class='d-block fs-16 fw-600 mb-2'>{{ $pp->getTranslation('name') }}</span>
                                                                                                        <span class='d-block opacity-50 fs-12'><i class='las la-map-marker'></i> {{ $pp->getTranslation('address') }}</span>
                                                                                                        <span class='d-block opacity-50 fs-12'><i class='las la-phone'></i>{{ $pp->phone }}</span>
                                                                                                      </span>">
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @if(get_setting('shipping_type') == 'carrier_wise_shipping')
                                                            <div class="row pt-3 carrier_id_{{ $sellerId }}">
                                                                @foreach($carrier_list as $i => $carrier)
                                                                    <div class="col-md-12 mb-2">
                                                                        <label class="aiz-megabox d-block bg-white mb-0">
                                                                            <input type="radio" name="carrier_id_{{ $sellerId }}"
                                                                                   value="{{ $carrier->id }}" {{ $i==0?'checked':'' }}>
                                                                            <span class="d-flex p-3 aiz-megabox-elem rounded-0">
                                                                                <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">
                                                                                    <img src="{{ uploaded_asset($carrier->logo) }}" class="w-50px img-fit">
                                                                                </span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ $carrier->name }}</span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ translate('Transit in').' '.$carrier->transit_time }}</span>
                                                                                <span class="flex-grow-1 pl-3 fw-600">{{ single_price(carrier_base_price($carts,$carrier->id,$sellerId)) }}</span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- PAYMENT -->
                        <div class="card rounded-0 mb-0 border shadow-none">
                            <div class="card-header border-bottom-0 py-3 py-xl-4" id="headingPaymentInfo"
                                 type="button" data-toggle="collapse" data-target="#collapsePaymentInfo"
                                 aria-expanded="true" aria-controls="collapsePaymentInfo">
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                        <path id="payment_check" d="M58,48A10,10,0,1,0,68,58,10,10,0,0,0,58,48ZM56.457,61.543a.663.663,0,0,1-.423.212.693.693,0,0,1-.428-.216l-2.692-2.692.856-.856,2.269,2.269,6-6.043.841.87Z"
                                              transform="translate(-48 -48)" fill="#9d9da6"/>
                                    </svg>
                                    <span class="ml-2 fs-19 fw-700">{{ translate('Payment') }}</span>
                                </div>
                                <i class="las la-angle-down fs-18"></i>
                            </div>
                            <div id="collapsePaymentInfo" class="collapse show"
                                 aria-labelledby="headingPaymentInfo" data-parent="#accordioncCheckoutInfo">
                                <div class="card-body" id="payment_info">
                                    @include('frontend.partials.cart.payment_info', ['carts' => $carts, 'total' => $total])

                                    <div class="pt-2rem fs-14">
                                        <label class="aiz-checkbox">
                                            <input type="checkbox" required id="agree_checkbox" onchange="validateAllSteps()">
                                            <span class="aiz-square-check"></span>
                                            <span>{{ translate('I agree to the') }}</span>
                                        </label>
                                        <a href="{{ route('terms') }}" class="fw-700">{{ translate('terms and conditions') }}</a>,
                                        <a href="{{ route('returnpolicy') }}" class="fw-700">{{ translate('return policy') }}</a> &
                                        <a href="{{ route('privacypolicy') }}" class="fw-700">{{ translate('privacy policy') }}</a>
                                    </div>

                                    <div class="row align-items-center pt-3 mb-4">
                                        <div class="col-6">
                                            <a href="{{ route('home') }}" class="btn btn-link fs-14 fw-700 px-0">
                                                <i class="las la-arrow-left fs-16"></i> {{ translate('Return to shop') }}
                                            </a>
                                        </div>
                                        <div class="col-6 text-right">
                                            <button type="button" id="submitOrderBtn"
                                                    class="btn btn-primary fs-14 fw-700 rounded-0 px-4" disabled>
                                                {{ translate('Complete Order') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4 mt-4 mt-lg-0" id="cart_summary">
                @include('frontend.partials.cart.cart_summary', ['proceed' => 0, 'carts' => $carts])
            </div>
        </div>
    </div>
</section>
@endsection

@section('modal')
    @if(Auth::check())
        @include('frontend.partials.address.address_modal')
    @endif
@endsection

@section('script')
<script type="text/javascript">
    // Global state
    let stepsValid = {
        shipping: false,
        delivery: false,
        payment: false
    };

    // Update button & icons
    function updateSubmitButton() {
        const allValid = stepsValid.shipping && stepsValid.delivery && stepsValid.payment;
        $('#submitOrderBtn').prop('disabled', !allValid);
    }

    function setStepValid(step, valid) {
        stepsValid[step] = valid;
        const color = valid ? '#15a405' : '#9d9da6';
        $(`#${step}_check`).css('fill', color);
        updateSubmitButton();
    }

    // SHIPPING VALIDATION
    function validateShipping() {
        let valid = false;
        @if(Auth::check())
            valid = $('input[name="address_id"]:checked').length > 0;
        @else
            valid = $('#shipping_info [required]').toArray().every(el => $(el).val().trim() !== '');
        @endif
        setStepValid('shipping', valid);
    }

    // DELIVERY VALIDATION
    function validateDelivery() {
        let valid = true;

        // Check delivery type radios
        $('input[name^="shipping_type_"]:checked').each(function () {
            const name = $(this).attr('name');
            const owner = name.match(/_(admin|\d+)$/)[1];
            const type = $(this).val();

            if (type === 'carrier' && $(`input[name="carrier_id_${owner}"]:checked`).length === 0) valid = false;
            if (type === 'pickup_point' && $(`select[name="pickup_point_id_${owner}"]`).val() === '') valid = false;
        });

        // Smart card check
        $('.renew-card').each(function () {
            if ($(this).prop('required') && !$(this).val().trim()) valid = false;
        });

        setStepValid('delivery', valid);
    }

    // PAYMENT VALIDATION
    function validatePayment() {
        const paymentSelected = $('input[name="payment_option"]:checked').length > 0;
        const offline = $('input[name="payment_option"]:checked').hasClass('offline_payment_option');
        const trxFilled = !offline || $('#trx_id').val().trim() !== '';
        const agreed = $('#agree_checkbox').is(':checked');
        setStepValid('payment', paymentSelected && trxFilled && agreed);
    }

    // MASTER VALIDATOR
    function validateAllSteps() {
        validateShipping();
        validateDelivery();
        validatePayment();
    }

    // SHOW/HIDE PICKUP & CARRIER
    window.show_pickup_point = function (el, user_id) {
        const type = $(el).val();
        const $target = $(el).data('target') ? $($(el).data('target')) : $(`.pickup_point_id_${user_id}`);

        if (type === 'home_delivery' || type === 'carrier') {
            $target.addClass('d-none');
            $(`.carrier_id_${user_id}`).removeClass('d-none');
        } else {
            $target.removeClass('d-none');
            $(`.carrier_id_${user_id}`).addClass('d-none');
        }

        // Update price via AJAX if needed
        const type_id = type === 'carrier' ? $(`input[name=carrier_id_${user_id}]:checked`).val() :
                        type === 'pickup_point' ? $(`select[name=pickup_point_id_${user_id}]`).val() : null;
        updateDeliveryInfo(type, type_id, user_id);
    };

    // FINAL SUBMIT
    function submitOrder(btn) {
        $(btn).prop('disabled', true);
        if (stepsValid.shipping && stepsValid.delivery && stepsValid.payment) {
            $('#checkout-form').submit();
        } else {
            AIZ.plugins.notify('danger', 'Please complete all steps.');
            $(btn).prop('disabled', false);
        }
    }

    // EVENT LISTENERS
    $(document).ready(function () {
        // Shipping changes
        $('#shipping_info').on('change', 'input, select', validateShipping);

        // Delivery changes
        $('#delivery_info').on('change', 'input, select', function () {
            const $radio = $(this).closest('input[name^="shipping_type_"]');
            if ($radio.length) {
                const userId = $radio.attr('name').match(/_(admin|\d+)$/)[1];
                show_pickup_point($radio[0], userId);
            }
            validateDelivery();
        });

        // Payment changes
        $(document).on('change', 'input[name="payment_option"], #trx_id, #agree_checkbox', validatePayment);

        // Initialize
        validateAllSteps();

        // Submit button click
        $('#submitOrderBtn').on('click', function () {
            submitOrder(this);
        });
    });
</script>

@include('frontend.partials.address.address_js')

@if(get_active_countries()->count() == 1)
<script>
    $(document).ready(function () {
        @if(get_setting('has_state') == 1)
            get_states(@json(get_active_countries()[0]->id));
        @else
            get_city_by_country(@json(get_active_countries()[0]->id));
        @endif
    });
</script>
@endif

@if (get_setting('google_map') == 1)
    @include('frontend.partials.google_map')
@endif
@endsection