<?php

namespace App\APIs;

use App\Models\Resources\Admission;
use App\Models\Resources\Ward;
use Illuminate\Support\Carbon;

class WardAPI implements \App\Contracts\WardAPI
{
    public function getWard(int|string $number = null): array
    {
        if ($number === null) {
            return Ward::query()
                ->select(['id', 'name'])
                ->withCount(['admissions' => fn ($q) => $q->whereNull('discharged_at')])
                ->having('admissions_count', '>', 0)
                ->get()
                ->transform(fn ($ward) => [
                    'number' => $ward->id,
                    'name' => $ward->name,
                    'admissions_count' => $ward->admissions_count,
                ])->toArray();
        }

        return Admission::query()
            ->where('ward_id', $number)
            ->whereNull('discharged_at')
            ->get()
            ->transform(function ($admission) {
                return [
                    'an' => $admission->an,
                    'hn' => $admission->hn,
                    'name' => $admission->name,
                    'gender' => $admission->gender,
                    'age' => $admission->age,
                    'age_unit' => $admission->age_unit,
                    'admitted_at' => $admission->admitted_at,
                ];
            })->toArray();
    }

    public function getAdmissionDischargeDate(array $data): array
    {
        $begin = Carbon::create($data['begin_date']);
        $end = Carbon::create($data['end_date'] ?? $begin->copy()->addDay());

        if (! ($data['number'] ?? false)) {
            return Ward::query()
                ->select(['id', 'name'])
                ->withCount([
                    'admissions' => fn ($query) => $query->whereBetween('discharged_at', [$begin, $end]),
                ])->having('admissions_count', '>', 0)
                ->get()
                ->transform(function ($ward) {
                    return [
                        'number' => $ward->id,
                        'name' => $ward->name,
                        'admissions_count' => $ward->admissions_count,
                    ];
                })->toArray();
        }

        return Admission::query()
            ->where('ward_id', $data['number'])
            ->whereBetween('discharged_at', [$begin, $end])
            ->get()
            ->transform(function ($admission) {
                return [
                    'an' => $admission->an,
                    'hn' => $admission->hn,
                    'name' => $admission->name,
                    'gender' => $admission->gender,
                    'age' => $admission->age,
                    'age_unit' => $admission->age_unit,
                    'discharge_status' => $admission->discharge_status_name,
                    'discharge_type' => $admission->discharge_type_name,
                    'admitted_at' => $admission->admitted_at,
                    'discharged_at' => $admission->discharged_at,
                ];
            })->toArray();
    }
}
