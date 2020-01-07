<?php

namespace App\Modules\Core\Patients\Clients\API\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Core\PatientCards\PatientCards;
use App\Modules\Core\Patients\Clients\API\Requests\SearchPatientsRequest;
use App\Modules\Core\Patients\Clients\API\Requests\StorePatientRequest;
use App\Modules\Core\Patients\Clients\API\Requests\UpdatePatientRequest;
use App\Modules\Core\Patients\Clients\API\Resources\PatientResource;
use App\Modules\Core\Patients\Data\Models\Patient;
use App\Modules\Core\Patients\Processing\Actions\CreatePatientAction;
use App\Modules\Core\Patients\Processing\Actions\GetAllPatientsAction;
use App\Modules\Core\Patients\Processing\Actions\SearchPatientsAction;
use App\Modules\Core\Patients\Processing\Actions\UpdatePatientAction;
use App\Modules\Core\PatientSteps\PatientSteps;
use Illuminate\Support\Facades\App;


use Illuminate\Support\Facades\DB;

class PatientAPIController extends  Controller
{
    public function getAll()
    {
        return PatientResource::collection(App::make(GetAllPatientsAction::class)->run());
    }

    public function get(Patient $patient)
    {
        return new PatientResource($patient);
    }

    public function store(StorePatientRequest $request)
    {
        return new PatientResource(App::make(CreatePatientAction::class)->run($request->all()));
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        return new PatientResource(App::make(UpdatePatientAction::class)->run($request->all(), $patient));
    }

    public function jsonFormat($data)
    {
        $processed = array();
        /*{"patient_id":2126,"guardian_name":"Benard Phiri","patient_phone":null,"guardian_phone":null,"follow_up":null,"guardian_relation":"Relative","soldier":0,"creator":1,"date_created":"2019-10-24 10:31:47","changed_by":null,"date_changed":null,"voided":0,"voided_by":null,"date_voided":null,"void_reason":null}*/
        $sql = "
SELECT
    p.person_id patient_id, n.given_name, n.middle_name, n.family_name,
    p.birthdate, p.birthdate_estimated, p.gender,  p.dead, p.death_date, p.date_created,
    a.city_village village, i.identifier arv_number, i2.identifier national_health_id, i.patient_identifier_id
FROM emastercard.person p
LEFT JOIN person_name n ON n.person_id = p.person_id AND n.voided = 0
LEFT JOIN person_address a ON a.person_id = p.person_id AND a.voided = 0
LEFT JOIN patient_identifier i On i.patient_id = p.person_id AND i.voided = 0
AND i.identifier_type = (SELECT patient_identifier_type_id FROM patient_identifier_type WHERE name = 'ARV Number')
LEFT JOIN patient_identifier i2 On i2.patient_id = p.person_id AND i2.voided = 0
AND i2.identifier_type = (SELECT patient_identifier_type_id FROM patient_identifier_type WHERE name = 'National id')
WHERE p.voided = 0 AND p.person_id=";


        for ($i=0; $i < sizeof($data); $i++) { 

            $results = DB::select(DB::raw($sql.$data[$i]->patient_id." LIMIT 1"));
            $person = array(
                "object" => "PersonResource",
                "person_id" => $data[$i]->patient_id,
                "personName"=>array(
                    "prefix"=> null,
                    "given"=>$results[0]->given_name,
                    "middle"=> $results[0]->middle_name,
                    "family"=> $results[0]->family_name
                )
                
                
            );
            
            array_push($processed, array(

                'object' => 'PatientResource' ,
                'Patient_id' => $results[0]->patient_id ,
                'patientIdentifierID' => $results[0]->patient_identifier_id,
                'artNumber' => $results[0]->arv_number,
                'fullArtNumber' => $results[0]->arv_number,
                'lastVisitDate' => null,
                'guardianName' => $data[$i]->guardian_name,
                'patientPhone' => null,
                'guardianPhone' => null,
                'followUp' => null,
                'soldier' => 0,
                'guardianRelation' => null,
                'person' => $person,
                'guardianRelation' => null,
                'guardianRelation' => null,
                'guardianRelation' => null,


            ));

            
        }
        /*
        'object' => 'PatientResource',
            'patientID' => $this->patient_id,
            'patientIdentifierID' => $this->patient_identifier_id,
            'artNumber' => $this->art_number,
            'fullArtNumber' => $this->full_art_number,
            'lastVisitDate' => $this->last_visit_date,
            'guardianName' => $this->guardian_name,
            'patientPhone' => $this->patient_phone,
            'guardianPhone' => $this->guardian_phone,
            'followUp' => $this->follow_up,
            'soldier' => (int)$this->soldier,
            'guardianRelation' => $this->guardian_relation,
            'person' => Persons::resource($this->person),
            'dateCreated' => (string)$this->date_created,*/
        $tosend = array('data' => $processed );
        return $tosend;
    }
    public function search(SearchPatientsRequest $request)
    {
        $patients = App::make(SearchPatientsAction::class)->run($request->all());
        //die('Trave');

        return json_encode($this->jsonFormat($patients));
        //return PatientResource::collection($patients);
    }

    public function getCards(Patient $patient)
    {
        return PatientCards::resourceCollection($patient->patientCards);
    }
}
