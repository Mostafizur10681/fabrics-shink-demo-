<?php

namespace Tests\Feature;

use App\Models\FabricRoll;
use App\Models\ShrinkageChart;
use App\Services\FabricShrinkageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FabricShrinkageTest extends TestCase
{
    protected FabricShrinkageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FabricShrinkageService();
    }

    /**
     * Test average calculation for 6 shrinkage readings.
     */
    public function test_six_reading_averages_calculation()
    {
        $l1 = -3.50; $l2 = -3.40; $l3 = -3.60;
        $w1 = -2.50; $w2 = -2.40; $w3 = -2.60;

        $averages = $this->service->calculateAverages($l1, $l2, $l3, $w1, $w2, $w3);

        $this->assertEquals(-3.50, $averages['length_avg']);
        $this->assertEquals(-2.50, $averages['width_avg']);
    }

    /**
     * Test Group 23 classification for worked example.
     */
    public function test_group_23_worked_example_classification()
    {
        $result = $this->service->classify(-3.50, -2.50, 'Washed');

        $this->assertEquals('MATCHED', $result['status']);
        $this->assertEquals('Group 23', $result['matched_group']);
        $this->assertFalse($result['is_boundary_match']);
    }

    /**
     * Test exact upper boundary matching for Group 23 (-3.00, -2.00).
     */
    public function test_exact_upper_boundary_matching()
    {
        $result = $this->service->classify(-3.00, -2.00, 'Washed');

        $this->assertEquals('MATCHED', $result['status']);
        $this->assertEquals('Group 23', $result['matched_group']);
        $this->assertTrue($result['is_boundary_match']);
    }

    /**
     * Test exact lower boundary matching for Group 23 (-4.00, -3.00).
     */
    public function test_exact_lower_boundary_matching()
    {
        $result = $this->service->classify(-4.00, -3.00, 'Washed');

        $this->assertEquals('MATCHED', $result['status']);
        $this->assertEquals('Group 23', $result['matched_group']);
        $this->assertTrue($result['is_boundary_match']);
    }

    /**
     * Test no matching group scenario.
     */
    public function test_no_matching_group()
    {
        $result = $this->service->classify(-15.00, -11.00, 'Washed');

        $this->assertEquals('NO_MATCH', $result['status']);
        $this->assertNull($result['matched_group']);
    }

    /**
     * Test multiple matching groups scenario.
     */
    public function test_multiple_matching_groups()
    {
        $result = $this->service->classify(-2.20, -1.20, 'Washed');

        $this->assertEquals('MULTIPLE_MATCHES', $result['status']);
        $this->assertStringContainsString('Group 99-A', $result['matched_group']);
        $this->assertStringContainsString('Group 99-B', $result['matched_group']);
    }

    /**
     * Test API endpoint: Dashboard stats.
     */
    public function test_api_dashboard_stats()
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'summary' => [
                    'total_rolls',
                    'total_chart_rules',
                    'matched_count',
                    'no_match_count',
                    'multiple_matches_count',
                ],
                'group_distribution',
                'stage_distribution',
                'scatter_points',
            ]);
    }

    /**
     * Test API endpoint: Create Fabric Roll via API.
     */
    public function test_api_create_fabric_roll()
    {
        $rollNumber = 'TEST-API-ROLL-' . uniqid();
        $payload = [
            'roll_number' => $rollNumber,
            'lot_number' => 'LOT-TEST-001',
            'fabric_type' => '100% Cotton Twill',
            'buyer' => 'Test Buyer',
            'order_number' => 'PO-99999',
            'stage' => 'Washed',
            'roll_length' => 100.0,
            'test_date' => '2026-11-13',
            'tested_by' => 'Automated Test Runner',
            'length_reading_1' => -3.50,
            'length_reading_2' => -3.40,
            'length_reading_3' => -3.60,
            'width_reading_1' => -2.50,
            'width_reading_2' => -2.40,
            'width_reading_3' => -2.60,
            'notes' => 'API test insertion',
        ];

        $response = $this->postJson('/api/fabric-rolls', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.roll_number', $rollNumber)
            ->assertJsonPath('data.length_shrinkage_avg', -3.5)
            ->assertJsonPath('data.width_shrinkage_avg', -2.5)
            ->assertJsonPath('data.matched_group', 'Group 23')
            ->assertJsonPath('data.classification_status', 'MATCHED');
    }

    /**
     * Test API endpoint: Preview classification without saving.
     */
    public function test_api_preview_classification()
    {
        $payload = [
            'length_reading_1' => -3.50,
            'length_reading_2' => -3.40,
            'length_reading_3' => -3.60,
            'width_reading_1' => -2.50,
            'width_reading_2' => -2.40,
            'width_reading_3' => -2.60,
            'stage' => 'Washed',
        ];

        $response = $this->postJson('/api/fabric-rolls/preview', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('averages.length_avg', -3.5)
            ->assertJsonPath('averages.width_avg', -2.5)
            ->assertJsonPath('classification.matched_group', 'Group 23')
            ->assertJsonPath('classification.status', 'MATCHED');
    }

    /**
     * Test API endpoint: Shrinkage Charts list.
     */
    public function test_api_shrinkage_charts_list()
    {
        $response = $this->getJson('/api/shrinkage-charts?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'group_name',
                        'stage',
                        'length_min',
                        'length_max',
                        'width_min',
                        'width_max',
                    ]
                ],
                'total'
            ]);

        $this->assertGreaterThanOrEqual(400, $response->json('total'));
    }
}

