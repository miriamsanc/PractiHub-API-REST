<?php


use App\Models\User;
use App\Models\Offer;
use App\Models\Application;
use Laravel\Passport\Passport;
 
it('ranks companies by acceptance rate, highest first', function () {
    // Empresa A: 3 aceptadas, 1 rechazada -> 75%
    $companyA = User::factory()->create(['role' => 'company', 'name' => 'Empresa A']);
    $offerA = Offer::factory()->create(['user_id' => $companyA->id]);
    Application::factory()->create(['offer_id' => $offerA->id, 'status' => 'accepted']);
    Application::factory()->create(['offer_id' => $offerA->id, 'status' => 'accepted']);
    Application::factory()->create(['offer_id' => $offerA->id, 'status' => 'accepted']);
    Application::factory()->create(['offer_id' => $offerA->id, 'status' => 'rejected']);
 
    // Empresa B: 1 aceptada, 3 rechazadas -> 25%
    $companyB = User::factory()->create(['role' => 'company', 'name' => 'Empresa B']);
    $offerB = Offer::factory()->create(['user_id' => $companyB->id]);
    Application::factory()->create(['offer_id' => $offerB->id, 'status' => 'accepted']);
    Application::factory()->create(['offer_id' => $offerB->id, 'status' => 'rejected']);
    Application::factory()->create(['offer_id' => $offerB->id, 'status' => 'rejected']);
    Application::factory()->create(['offer_id' => $offerB->id, 'status' => 'rejected']);
 
    Passport::actingAs(User::factory()->create(['role' => 'student']));
 
    $response = $this->getJson('/api/companies/ranking');
 
    $response->assertStatus(200);
 
    $names = collect($response->json('data'))->pluck('name')->values()->all();
    expect($names)->toBe(['Empresa A', 'Empresa B']);
 
    $response->assertJsonPath('data.0.acceptance_rate', 75)
             ->assertJsonPath('data.1.acceptance_rate', 25);
});
 
it('ignores pending and read applications when calculating the acceptance rate', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
 
    Application::factory()->create(['offer_id' => $offer->id, 'status' => 'accepted']);
    Application::factory()->create(['offer_id' => $offer->id, 'status' => 'pending']);
    Application::factory()->create(['offer_id' => $offer->id, 'status' => 'read']);
 
    Passport::actingAs(User::factory()->create(['role' => 'student']));
 
    $response = $this->getJson('/api/companies/ranking');
 
    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.acceptance_rate', 100)
             ->assertJsonPath('data.0.total_resolved', 1);
});
 
it('counts applications from inactive (closed) offers too', function () {
    $company = User::factory()->create(['role' => 'company']);
    $closedOffer = Offer::factory()->create(['user_id' => $company->id, 'is_active' => false]);
 
    Application::factory()->create(['offer_id' => $closedOffer->id, 'status' => 'accepted']);
 
    Passport::actingAs(User::factory()->create(['role' => 'student']));
 
    $response = $this->getJson('/api/companies/ranking');
 
    $response->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.acceptance_rate', 100);
});
 
it('excludes companies with no resolved applications from the ranking', function () {
    $companyWithHistory = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $companyWithHistory->id]);
    Application::factory()->create(['offer_id' => $offer->id, 'status' => 'accepted']);
 
    // Empresa sin ninguna candidatura resuelta (solo pending)
    $companyWithoutHistory = User::factory()->create(['role' => 'company']);
    $otherOffer = Offer::factory()->create(['user_id' => $companyWithoutHistory->id]);
    Application::factory()->create(['offer_id' => $otherOffer->id, 'status' => 'pending']);
 
    // Empresa sin ninguna oferta ni candidatura
    User::factory()->create(['role' => 'company']);
 
    Passport::actingAs(User::factory()->create(['role' => 'student']));
 
    $response = $this->getJson('/api/companies/ranking');
 
    $response->assertStatus(200)->assertJsonCount(1, 'data');
});
 
it('allows both students and companies to view the ranking', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
    Application::factory()->create(['offer_id' => $offer->id, 'status' => 'accepted']);
 
    Passport::actingAs(User::factory()->create(['role' => 'company']));
    $this->getJson('/api/companies/ranking')->assertStatus(200);
 
    Passport::actingAs(User::factory()->create(['role' => 'student']));
    $this->getJson('/api/companies/ranking')->assertStatus(200);
});
 
it('forbids unauthenticated users from viewing the ranking', function () {
    $response = $this->getJson('/api/companies/ranking');
 
    $response->assertStatus(401);
});