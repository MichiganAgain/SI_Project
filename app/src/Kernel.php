<?php
/**
 * Base Kernel.
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Base Kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
