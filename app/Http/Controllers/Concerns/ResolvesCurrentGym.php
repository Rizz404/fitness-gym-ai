<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Gym;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResolvesCurrentGym
{
    protected function currentGym(Request $request): Gym
    {
        $gym = $request->user()->currentGym();

        if (! $gym) {
            throw new HttpException(403, 'No gym is linked to this account yet.');
        }

        return $gym;
    }
}
