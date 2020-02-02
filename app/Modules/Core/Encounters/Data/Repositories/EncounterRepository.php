<?php

namespace App\Modules\Core\Encounters\Data\Repositories;

use App\Modules\Core\Encounters\Data\Models\Encounter;
use App\Modules\Core\EncounterTypes\Data\Models\EncounterType;
use App\Modules\Core\Patients\Data\Models\Patient;
use App\Modules\Core\Persons\Data\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EncounterRepository {

    public function getAll()
    {
        return Encounter::all();
    }

    public function get($id)
    {
        return Encounter::find($id);
    }

    public function getBy($field, $value)
    {
        return Encounter::where($field, $value)->first();
    }

    public function create(Patient $patient, EncounterType $encounterType, $encounterDatetime, Person $person)
    {
        $encounter = new Encounter;
        $encounter->encounter_datetime = $encounterDatetime;

        $encounter->patient()->associate($patient);
        $encounter->type()->associate($encounterType);
        $encounter->provider()->associate($person);

        $encounter->save();

        return $encounter;
    }

    public function update($data, Encounter $encounter, $encounterDatetime)
    {
        $encounter->encounter_datetime = $encounterDatetime;
        $encounter->update($data);

        return $encounter;
    }

    public function voidEncounterById($patient, $encounterType){
        return Encounter::where([
            ['patient_id', $patient['patient_id']],
            ['encounter_type', $encounterType['encounter_type_id']]
        ])->update(['voided' => 1]);
    }

    public function patientHasEncounter($patient, $encounterType){
        return Encounter::where([
            ['patient_id', $patient['patient_id']],
            ['encounter_type', $encounterType['encounter_type_id']]
        ])->exists();
    }

    public function findEncounterById($patient, $encounterType){
        return Encounter::where([
            ['patient_id', $patient['patient_id']],
            ['encounter_type', $encounterType['encounter_type_id']]
        ])
        ->latest('encounter_id', 'ASC')
        ->first();
    }

    public function voidEncounterByIdConceptId($params){

        $encounters = DB::table('encounter')
            ->join('visit_outcome_event', 'encounter.encounter_id', '=', 'visit_outcome_event.encounter_id')
            ->join('obs', 'encounter.encounter_id', '=', 'obs.encounter_id')
            ->where([
                ['encounter.patient_id', $params['patient']],
                ['encounter.encounter_type', $params['type']]
            ])->whereIn('obs.concept_id', [$params['concepts']])
            ->update(['encounter.voided' => 1]); 

    }

    public function findRecentEncounterByType($params){
        return DB::table('encounter')
            ->join('visit_outcome_event', 'encounter.encounter_id', '=', 'visit_outcome_event.encounter_id')
            ->join('obs', 'encounter.encounter_id', '=', 'obs.encounter_id')
            ->where([
                ['encounter.patient_id', $params['patient']],
                ['encounter.encounter_type', $params['type']]
            ])
            ->whereIn('obs.concept_id', [$params['concepts']])
            ->latest('encounter.encounter_id', 'DESC')
            ->first();
    }

    public function unVoidEncounterById($params){

        return Encounter::where([
            ['patient_id', $params['params']['patient']],
            ['encounter_id', $params['unvoid_encounter_id']],
            ['encounter_type', $params['params']['type']]
        ])
        ->update(['voided' => 0]);

    }
}