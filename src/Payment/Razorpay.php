<?php

namespace Wontonee\Razorpay\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Is available.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if (!$this->cart) {
            $this->setCart();
        }

        return $this->getConfigData('active') && $this->cart?->haveStockableItems();
    }

    /**
     * Get payment method image.
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : '';
        
    }
}
