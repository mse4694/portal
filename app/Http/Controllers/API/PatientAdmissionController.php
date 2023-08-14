<?php

namespace App\Http\Controllers\API;

use App\Contracts\AdmissionAPI;
use App\Http\Controllers\Controller;
use App\Traits\ServiceAccessLoggable;
use Illuminate\Http\Request;

class PatientAdmissionController extends Controller
{
    use ServiceAccessLoggable;

    public function __invoke(Request $request, AdmissionAPI $api)
    {
        $validated = $request->validate([
            'hn' => ['required', 'digits:8'],
        ]);

        $withSensitiveInfo = $request->route()->getName() === 'api.patient-admissions-with-sensitive-data';
        $data = $api->getPatientAdmissions($validated['hn'], $withSensitiveInfo);
        $this->log(
            $request->bearerToken(),
            $validated,
            $request->route()->getName(),
            $data['found'] ?? false,
        );

        return $data;
    }
}
