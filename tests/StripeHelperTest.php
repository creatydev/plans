<?php

namespace Rennokki\Plans\Test;

use Rennokki\Plans\Helpers\StripeHelper;

class StripeHelperTest extends TestCase
{
    /**
     * @group stripe
     */
    public function testIsZeroDecimalCurency()
    {
        foreach (StripeHelper::$stripeZeroDecimalCurrencies as $currency) {
            $this->assertTrue(StripeHelper::isZeroDecimalCurrency($currency));
        }
    }

    /**
     * @group stripe
     */
    public function testFromStripeToReal()
    {
        foreach (StripeHelper::$stripeZeroDecimalCurrencies as $currency) {
            $this->assertEquals(StripeHelper::fromStripeAmountToReal(100, $currency), 100);
        }

        $this->assertEquals(StripeHelper::fromStripeAmountToreal(100, 'USD'), 1.00);
        $this->assertEquals(StripeHelper::fromStripeAmountToreal(100, 'EUR'), 1.00);
        $this->assertEquals(StripeHelper::fromStripeAmountToreal(123, 'USD'), 1.23);
        $this->assertEquals(StripeHelper::fromStripeAmountToreal(123, 'EUR'), 1.23);
    }

    /**
     * @group stripe
     */
    public function testFromRealToStripe()
    {
        foreach (StripeHelper::$stripeZeroDecimalCurrencies as $currency) {
            $this->assertEquals(StripeHelper::fromRealAmountToStripe(100, $currency), 100);
        }

        $this->assertEquals(StripeHelper::fromRealAmountToStripe(1.00, 'USD'), 100);
        $this->assertEquals(StripeHelper::fromRealAmountToStripe(1.00, 'EUR'), 100);
        $this->assertEquals(StripeHelper::fromRealAmountToStripe(1.23, 'USD'), 123);
        $this->assertEquals(StripeHelper::fromRealAmountToStripe(1.23, 'EUR'), 123);
    }
}
