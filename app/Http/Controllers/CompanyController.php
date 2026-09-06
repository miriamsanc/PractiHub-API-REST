<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanyRankingResource;

class CompanyController extends Controller
{
    /**
     * @group Companies
     * 
     * Company Ranking
     * 
     * Retrieves a list of companies ordered by their application acceptance rate (highest first).
     * Excludes companies with no resolved (accepted or rejected) applications.
     * 
     * @authenticated
     */
    public function ranking()
    {
        $companies = User::where('role', 'company')
            ->withCount([
                'applicationsReceived as accepted_count' => function ($query) {
                    $query->where('status', 'accepted');
                },
                'applicationsReceived as rejected_count' => function ($query) {
                    $query->where('status', 'rejected');
                },
            ])
            ->get()
            // Excluimos empresas sin ninguna candidatura resuelta todavía
            ->filter(function ($company) {
                return ($company->accepted_count + $company->rejected_count) > 0;
            })
            ->map(function ($company) {
                $total = $company->accepted_count + $company->rejected_count;
                $company->acceptance_rate = round(($company->accepted_count / $total) * 100, 2);
                return $company;
            })
            ->sortByDesc('acceptance_rate')
            ->values();
 
        return CompanyRankingResource::collection($companies);
    }

    /**
     * @group Companies
     * 
     * Get company profile
     * 
     * Retrieves the details of a specific company account.
     * 
     * @authenticated
     */
    public function show(User $company): CompanyResource
    {
        // Asegurarnos de que el usuario solicitado es una empresa
        if ($company->role !== 'company') {
            abort(404, 'Company not found');
        }
        
        
        return new CompanyResource($company);
    }

    /**
     * @group Companies
     * 
     * Update company profile
     * 
     * Modifies the information of the authenticated company.
     * 
     * @authenticated
     */
    public function update(UpdateCompanyRequest $request, User $company): CompanyResource
    {
        // Verificamos que el perfil a editar sea una empresa
        if ($company->role !== 'company') {
            abort(404, 'Company not found');
        }
    
        // AUTORIZACIÓN VÍA POLICY: Solo el dueño de la cuenta puede editarla
        // Si no es el dueño, detiene la ejecución y devuelve un 403.
        Gate::authorize('update', $company);
        
        $company->update($request->validated());

        return new CompanyResource($company);
    }

    /**
     * @group Companies
     * 
     * Delete company account
     * 
     * Permanently removes the authenticated company's account from the database.
     * 
     * @authenticated
     */
    public function destroy(User $company): JsonResponse
    {
        if ($company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }
    
        // AUTORIZACIÓN CON POLICY
        Gate::authorize('delete', $company);

        // ELIMINACIÓN
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);
    }
}
