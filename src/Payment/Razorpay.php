<?php

namespace Wontonee\Razorpay\Payment;

use Webkul\Payment\Payment\Payment;

class Razorpay extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'razorpay';

    public function getRedirectUrl()
    {
        return route('razorpay.process');
        
    }
}