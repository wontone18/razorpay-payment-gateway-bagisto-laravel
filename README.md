# Bagisto Razorpay Payment Gateway
Razorpay is a popular payment gateway in India. This package provides strong support for users to integrate the Razorpay payment gateway into their Bagisto Laravel e-commerce applications.

---

## Licensing Information

You must take a license from the website [https://myapps.wontonee.com/login](https://myapps.wontonee.com/login), either trial or paid.

- **Trial License**: Works for 7 days.
- **Paid License**: Valid for 1 year and costs only ₹400.  
  Includes updates and support.

## How to Get a License Key

You can watch the video tutorial below to learn how to get a license key:

[![Watch the video](https://img.youtube.com/vi/E4NTZ4TyM5M/0.jpg)](https://youtu.be/E4NTZ4TyM5M?si=uIUXfeaj0ttH7VhC)


## Compatibility Notice
**<span style="color:red;">Support Bagisto v2.2. For Bagisto 2.1, you can downgrade the package to 4.2.2</span>**

**<span style="color:red;">From 15 January 2025, you must have a valid license key to use this extension. It costs only ₹400/year, including updates and support. Use this link to get your license key: [Get License Key](https://pages.razorpay.com/pl_PcXc750AtzmCEE/view)</span>**

## Installation

1. Use the command prompt to install this package:
```sh
composer require wontonee/razorpay
```

2. Open `config/app.php` and register the Razorpay provider:
```sh
'providers' => [
        // Razorpay provider
        Wontonee\Razorpay\Providers\RazorpayServiceProvider::class,
]
```
3. Navigate to the `admin panel -> Configure/Payment Methods`, where Razorpay will be visible at the end of the payment method list.

4. Open app\Http\Middleware\VerifyCsrfToken.php and add this route to the exception list:
```sh
protected $except = [
                  '/razorpaycheck',
           ];
```

5. Now run 
```sh
php artisan config:cache
```

## Troubleshooting

1. If you encounter an issue where you are not redirected to the payment gateway after placing an order and receive a route error, navigate to `bootstrap/cache` and delete all cache files.


For any help or customization, visit <https://www.wontonee.com> or email us <dev@wontonee.com>
