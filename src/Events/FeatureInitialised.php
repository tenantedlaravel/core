<?php
declare(strict_types=1);

namespace Tenanted\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * @method static FeatureInitialised dispatch(string $feature)
 * @method static FeatureInitialised dispatchIf(bool $boolean, string $feature)
 * @method static FeatureInitialised dispatchUnless(bool $boolean, string $feature)
 */
final class FeatureInitialised
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