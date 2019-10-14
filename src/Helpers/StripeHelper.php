<?php

namespace Rennokki\Plans\Helpers;

class StripeHelper
{
    public static $stripeZeroDecimalCurrencies = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW',
        'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Check if the currency is zero decimal currency.
     *
     * @return bool
     */
    public static function isZeroDecimalCurrency(string $currency)
    {
        return (bool) in_array($currency, Self::$stripeZeroDecimalCurrencies);
    }

    /**
     * Transform the Stripe amount to amount with decimals.
     *
     * @param float $amount The amount used.
     * @param string $currency The currency.
     * @return float
     */
    public static function fromStripeAmountToReal(float $amount, string $currency): float
    {
        if (! Self::isZeroDecimalCurrency($currency)) {
            return (float) ($amount / 100);
        }

        return (float) $amount;
    }

    /**
     * Transform the amount used with decimals into amount usable for Stripe charges.
     *
     * @param float $amount The amount used.
     * @param string $currency The currency.
     * @return float
     */
    public static function fromRealAmountToStripe(float $amount, string $currency): float
    {
        if (! Self::isZeroDecimalCurrency($currency)) {
            return (float) ($amount * 100);
        }

        return (float) $amount;
    }
}
