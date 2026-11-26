<?php

namespace Tests\Unit;

use App\Services\PhoneNormalizer;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    public function test_normalizes_zero_prefix(): void
    {
        $this->assertSame('628123456789', PhoneNormalizer::normalize('08123456789'));
    }

    public function test_normalizes_plus_sixty_two(): void
    {
        $this->assertSame('628123456789', PhoneNormalizer::normalize('+62 812-345-6789'));
    }

    public function test_keeps_sixty_two_prefix(): void
    {
        $this->assertSame('628123456789', PhoneNormalizer::normalize('628123456789'));
    }

    public function test_rejects_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PhoneNormalizer::normalize('123');
    }
}
