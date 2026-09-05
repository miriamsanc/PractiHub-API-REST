<?php

use App\Models\Offer;
use App\Models\User;
use App\Models\Application;
use Laravel\Passport\Passport;

//CREATE//

it('allows a student to apply for an offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);
    //metodo aux que equivale al 201
    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.cv_link', 'https://example.com/cv.pdf');

    $this->assertDatabaseHas('applications', [
        'user_id' => $student->id,
        'offer_id' => $offer->id,
        'status' => 'pending',
        'cv_path' => 'https://example.com/cv.pdf',
    ]);
});

it('fails to apply when cv_path is not a valid URL', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'invalid-url',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['cv_path']);
});

it('fails to apply when cv_path is missing', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['cv_path']);
});

it('fails when cv_path exceeds 255 characters', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($student);

    $cvPath = 'https://example.com/' . str_repeat('a', 250);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => $cvPath,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['cv_path']);
});


it('forbids a student from applying twice to the same offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => true]);

    // Ya está inscrito
    Application::factory()->create([
        'user_id' => $student->id,
        'offer_id' => $offer->id,
    ]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);

    $response->assertStatus(409)
        ->assertJson(['message' => 'You have already applied to this offer']);
    
});


it('prevents applying to an inactive offer', function () {
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['is_active' => false]);

    Passport::actingAs($student);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => 'This offer is no longer open']);
});

it('forbids a company from applying to an offer', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['is_active' => true]);

    Passport::actingAs($company);

    $response = $this->postJson("/api/offers/{$offer->id}/applications", [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);

    $response->assertStatus(403);
});

it('returns 404 when applying to a non-existent offer', function () {
    $student = User::factory()->create(['role' => 'student']);

    Passport::actingAs($student);

    $response = $this->postJson('/api/offers/999999/applications', [
        'cv_path' => 'https://example.com/cv.pdf',
    ]);

    $response->assertStatus(404);
});

//READ//

it('returns 404 when viewing a non-existent application', function () {
    $student = User::factory()->create(['role' => 'student']);

    Passport::actingAs($student);

    $response = $this->getJson('/api/applications/999999');

    $response->assertStatus(404);
});

it('returns only student own applications', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);

    $app1 = Application::factory()->create(['user_id' => $student1->id]);
    Application::factory()->create(['user_id' => $student2->id]);

    Passport::actingAs($student1);

    $response = $this->getJson('/api/applications');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $app1->id);
});

it('returns applications submitted to the company offers', function () {
    $company1 = User::factory()->create(['role' => 'company']);
    $company2 = User::factory()->create(['role' => 'company']);

    $offer1 = Offer::factory()->create(['user_id' => $company1->id]);
    $offer2 = Offer::factory()->create(['user_id' => $company2->id]);

    $app1 = Application::factory()->create(['offer_id' => $offer1->id]);
    Application::factory()->create(['offer_id' => $offer2->id]);

    Passport::actingAs($company1);

    $response = $this->getJson('/api/applications');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $app1->id);
});

it('allows student or offer owner company to view application detail', function () {
    $company = User::factory()->create(['role' => 'company']);
    $student = User::factory()->create(['role' => 'student']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
    $app = Application::factory()->create(['user_id' => $student->id, 'offer_id' => $offer->id]);

    Passport::actingAs($student);
    $this->getJson("/api/applications/{$app->id}")->assertOk();

    Passport::actingAs($company);
    $this->getJson("/api/applications/{$app->id}")->assertOk();
});

it('forbids another user from viewing application detail', function () {
    $otherStudent = User::factory()->create(['role' => 'student']);
    $app = Application::factory()->create();

    Passport::actingAs($otherStudent);

    $this->getJson("/api/applications/{$app->id}")->assertStatus(403);
});

//UPDATE//

it('allows offer owner company to update application status', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
    $app = Application::factory()->create(['offer_id' => $offer->id, 'status' => 'pending']);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$app->id}", [
        'status' => 'accepted',
    ]);

    $response->assertOk()
        ->assertJsonPath('application.status', 'accepted');

    $this->assertDatabaseHas('applications', [
        'id' => $app->id,
        'status' => 'accepted',
    ]);
});

it('forbids updating status with an invalid value', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
    $app = Application::factory()->create(['offer_id' => $offer->id]);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$app->id}", [
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('forbids another company from updating application status', function () {
    $otherCompany = User::factory()->create(['role' => 'company']);
    $app = Application::factory()->create(['status' => 'pending']);

    Passport::actingAs($otherCompany);

    $this->putJson("/api/applications/{$app->id}", ['status' => 'accepted'])
        ->assertStatus(403);
});

it('forbids a student from updating application status', function () {
    $student = User::factory()->create(['role' => 'student']);

    $app = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'pending',
    ]);

    Passport::actingAs($student);

    $response = $this->putJson("/api/applications/{$app->id}", [
        'status' => 'accepted',
    ]);

    $response->assertStatus(403);
});

it('forbids updating application status to pending', function () {
    $company = User::factory()->create(['role' => 'company']);

    $application = Application::factory()->create([
        'status' => 'pending',
        'offer_id' => Offer::factory()->create([
            'user_id' => $company->id,
        ])->id,
    ]);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$application->id}", [
        'status' => 'pending',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('allows the offer owner company to set a valid application status', function (string $status) {
    $company = User::factory()->create(['role' => 'company']);

    $offer = Offer::factory()->create([
        'user_id' => $company->id,
    ]);

    $application = Application::factory()->create([
        'offer_id' => $offer->id,
        'status' => 'pending',
    ]);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$application->id}", [
        'status' => $status,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => $status,
    ]);
})->with([
    'read',
    'accepted',
    'rejected',
]);

it('forbids changing a pending application directly to accepted', function () {
    $company = User::factory()->create(['role' => 'company']);

    $offer = Offer::factory()->create([
        'user_id' => $company->id,
    ]);

    $application = Application::factory()->create([
        'offer_id' => $offer->id,
        'status' => 'pending',
    ]);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$application->id}", [
        'status' => 'accepted',
    ]);

    $response->assertStatus(400);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'pending',
    ]);
});

it('forbids changing a pending application directly to rejected', function () {
    $company = User::factory()->create(['role' => 'company']);

    $offer = Offer::factory()->create([
        'user_id' => $company->id,
    ]);

    $application = Application::factory()->create([
        'offer_id' => $offer->id,
        'status' => 'pending',
    ]);

    Passport::actingAs($company);

    $response = $this->putJson("/api/applications/{$application->id}", [
        'status' => 'rejected',
    ]);

    $response->assertStatus(400);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'pending',
    ]);
});

//DELETE//

it('allows student to withdraw application within 30 minutes', function () {
    $student = User::factory()->create(['role' => 'student']);
    $app = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'pending',
        'created_at' => now(),
    ]);

    Passport::actingAs($student);

    $response = $this->deleteJson("/api/applications/{$app->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('applications', ['id' => $app->id]);
});

it('prevents withdrawal if 30 minutes have passed', function () {
    $student = User::factory()->create(['role' => 'student']);
    $app = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'pending',
        'created_at' => now()->subMinutes(31),
    ]);

    Passport::actingAs($student);

    $response = $this->deleteJson("/api/applications/{$app->id}");

    $response->assertStatus(400)
        ->assertJson(['message' => 'Time limit exceeded. You can only withdraw your application within the first 30 minutes.']);
});

it('prevents withdrawal if status is accepted', function () {
    $student = User::factory()->create(['role' => 'student']);
    $application = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'accepted',
        'created_at' => now(),
    ]);

    Passport::actingAs($student);

    $response = $this->deleteJson("/api/applications/{$application->id}");

    $response->assertStatus(400)
        ->assertJson(['message' => 'You cannot withdraw an application that has already been processed.']);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'accepted',
    ]);
});

it('prevents withdrawal if status is read', function () {
    $student = User::factory()->create(['role' => 'student']);
    $application = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'read',
        'created_at' => now(),
    ]);

    Passport::actingAs($student);

    $response = $this->deleteJson("/api/applications/{$application->id}");

    $response->assertStatus(400)
        ->assertJson(['message' => 'You cannot withdraw an application that has already been processed.']);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'read',
    ]);
});

it('forbids a student from withdrawing another student application', function () {
    $owner = User::factory()->create(['role' => 'student']);

    $application = Application::factory()->create([
        'user_id' => $owner->id,
        'status' => 'pending',
    ]);

    $otherStudent = User::factory()->create(['role' => 'student']);

    Passport::actingAs($otherStudent);

    $response = $this->deleteJson("/api/applications/{$application->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
    ]);
});

it('prevents withdrawal if application is rejected', function () {
    $student = User::factory()->create(['role' => 'student']);

    $application = Application::factory()->create([
        'user_id' => $student->id,
        'status' => 'rejected',
        'created_at' => now(),
    ]);

    Passport::actingAs($student);

    $response = $this->deleteJson("/api/applications/{$application->id}");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'You cannot withdraw an application that has already been processed.',
        ]);

    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'rejected',
    ]);
});

//SHOW//

it('allows offer owner company to view applicants list', function () {
    $company = User::factory()->create(['role' => 'company']);
    $offer = Offer::factory()->create(['user_id' => $company->id]);
    Application::factory()->count(3)->create(['offer_id' => $offer->id]);

    Passport::actingAs($company);

    $response = $this->getJson("/api/offers/{$offer->id}/applications");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('forbids unauthorized users from viewing offer applicants list', function () {
    $company = User::factory()->create(['role' => 'company']);
    $otherCompany = User::factory()->create(['role' => 'company']);
    
    $offer = Offer::factory()->create(['user_id' => $company->id]);

    Passport::actingAs($otherCompany);

    $this->getJson("/api/offers/{$offer->id}/applications")
        ->assertStatus(403);
});
