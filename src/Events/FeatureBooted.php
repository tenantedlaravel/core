<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * @method static FeatureBooted dispatch(string $feature)
 * @method static FeatureBooted dispatchIf(bool $boolean, string $feature)
 * @method static FeatureBooted dispatchUnless(bool $boolean, string $feature)
 */
final class FeatureBooted
{
    use Dispatchable;

    /**
     * @var class-string<\Tenanted\Core\Contracts\Feature>
     */
    private string $feature;

    /**
     * @param class-string<\Tenanted\Core\Contracts\Feature> $feature
     */
    public function __construct(string $feature)
    {
        $this->feature = $feature;
    }

    /**
     * @return class-string<\Tenanted\Core\Contracts\Feature>
     */
    public function feature(): string
    {
        return $this->feature;
    }
}