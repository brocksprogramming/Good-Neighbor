<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MicroRequest extends Model
{
    protected $fillable = [
        'item_name',
        'description',
        'latitude',
        'longitude',
        'urgency',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $radius = 5): Builder
    {
        $angularRadius = $radius / 3959;
        $latitudeRadians = deg2rad($latitude);
        $latitudeDelta = rad2deg($angularRadius);

        $query->whereBetween('latitude', [
            max(-90, $latitude - $latitudeDelta),
            min(90, $latitude + $latitudeDelta),
        ]);

        if (abs($latitudeRadians) + $angularRadius < M_PI / 2) {
            $longitudeDelta = rad2deg(asin(min(1, sin($angularRadius) / cos($latitudeRadians))));
            $west = $longitude - $longitudeDelta;
            $east = $longitude + $longitudeDelta;

            if ($west < -180) {
                $query->where(fn (Builder $query) => $query
                    ->where('longitude', '>=', $west + 360)
                    ->orWhere('longitude', '<=', $east));
            } elseif ($east > 180) {
                $query->where(fn (Builder $query) => $query
                    ->where('longitude', '>=', $west)
                    ->orWhere('longitude', '<=', $east - 360));
            } else {
                $query->whereBetween('longitude', [$west, $east]);
            }
        }

        $distance = '(3959 * 2 * asin(min(1, sqrt(
            power(sin(radians(latitude - ?) / 2), 2)
            + cos(radians(?)) * cos(radians(latitude))
            * power(sin(radians(longitude - ?) / 2), 2)
        ))))';
        $bindings = [$latitude, $latitude, $longitude];

        return $query
            ->select('*')
            ->selectRaw("{$distance} as distance", $bindings)
            ->whereRaw("{$distance} <= CAST(? AS REAL)", [...$bindings, $radius])
            ->orderBy('distance');
    }
}
