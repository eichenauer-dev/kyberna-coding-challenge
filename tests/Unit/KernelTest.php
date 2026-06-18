<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KernelTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function testKernelBoots(): void
    {
        self::bootKernel();

        $this->assertInstanceOf(Kernel::class, self::$kernel);
        $this->assertSame('test', self::$kernel->getEnvironment());
    }
}
