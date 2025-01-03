<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Xtompie\Rensen\R;

class RTest extends TestCase
{
    public function testInitialValue()
    {
        $r = new R(fn() => 5);
        $this->assertSame(5, $r());
    }

    public function testUpdateValue()
    {
        $r = new R(fn() => 5);
        $this->assertSame(5, $r());

        $r(fn() => 10);
        $this->assertSame(10, $r());
    }

    public function testDependentValue()
    {
        $a = new R(fn() => 2);
        $b = new R(fn() => 3);
        $c = new R(fn() => $a() + $b());

        $this->assertSame(5, $c());

        $a(fn() => 10);
        $this->assertSame(13, $c());
    }

    public function testEffectExecution()
    {
        $a = new R(fn() => 2);
        $b = new R(fn() => 3);
        $c = new R(fn() => $a() + $b());

        $output = '';
        new R(function () use ($c, &$output) {
            $output .= "c: {$c()}
";
        });

        $this->assertStringContainsString("c: 5
", $output);

        $a(fn() => 10);
        $this->assertStringContainsString("c: 13
", $output);
    }

    public function testEffectRunsOnce()
    {
        $a = new R(fn() => 1);
        $b = new R(fn() => 2);

        $runCount = 0;
        new R(function () use ($a, $b, &$runCount) {
            $runCount++;
            $a() + $b();
        });

        $this->assertSame(1, $runCount);

        $a(fn() => 3);
        $this->assertSame(2, $runCount);

        $b(fn() => 4);
        $this->assertSame(3, $runCount);
    }

    public function testNullValueHandling()
    {
        $r = new R(fn() => null);
        $this->assertNull($r());

        $r(fn() => 42);
        $this->assertSame(42, $r());
    }

    public function testNonCallableValue()
    {
        $r = new R(fn() => "test");
        $this->assertSame("test", $r());

        $r(fn() => null);
        $this->assertNull($r());
    }

    public function testExceptionInCallback()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Test exception");

        $r = new R(fn() => throw new \Exception("Test exception"));
        $r();
    }

    public function testImmediateExecution()
    {
        $called = false;
        new R(function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }
}
