<?php

namespace Tests\Feature;

use App\Models\MicroRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MicroRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_without_assuming_the_visitors_location(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find help close to home')
            ->assertSee('Share your location to see open requests');
    }

    public function test_nearby_requests_are_filtered_by_distance_and_status(): void
    {
        $nearby = MicroRequest::create([
            'item_name' => 'Winter coat',
            'latitude' => 44.0521,
            'longitude' => -123.07,
            'urgency' => 'high',
        ]);
        MicroRequest::create([
            'item_name' => 'Far away supplies',
            'latitude' => 44.25,
            'longitude' => -123.0868,
            'urgency' => 'medium',
        ]);
        MicroRequest::create([
            'item_name' => 'Already fulfilled',
            'latitude' => 44.0521,
            'longitude' => -123.0868,
            'urgency' => 'low',
            'status' => 'fulfilled',
        ]);

        $this->post('/nearby', [
            'latitude' => 44.0521,
            'longitude' => -123.0868,
            'radius' => 1,
        ])->assertRedirect('/?radius=1');

        $this->get('/?radius=1')
            ->assertOk()
            ->assertSee('Winter coat')
            ->assertDontSee('Far away supplies')
            ->assertDontSee('Already fulfilled');

        $this->assertGreaterThan(0, $nearby->newQuery()->whereKey($nearby->id)->nearby(44.0521, -123.0868, 1)->first()->distance);
    }

    public function test_posting_a_request_rounds_its_location_and_sets_expiration(): void
    {
        $this->post('/requests', [
            'item_name' => 'Sleeping bag',
            'description' => 'A warm one for cold nights',
            'urgency' => 'immediate',
            'latitude' => 44.05214,
            'longitude' => -123.08684,
        ])->assertRedirect('/')
            ->assertSessionHas('success');

        $request = MicroRequest::firstOrFail();

        $this->assertSame(44.052, $request->latitude);
        $this->assertSame(-123.087, $request->longitude);
        $this->assertSame('open', $request->status);
        $this->assertTrue($request->expires_at->isFuture());
    }

    public function test_nearby_search_crosses_the_longitude_date_line(): void
    {
        MicroRequest::create([
            'item_name' => 'Across the date line',
            'latitude' => 0,
            'longitude' => 179.99,
            'urgency' => 'medium',
        ]);

        $results = MicroRequest::query()->nearby(0, -179.99, 2)->get();

        $this->assertCount(1, $results);
        $this->assertLessThan(2, $results->first()->distance);
    }
}
