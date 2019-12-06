<?php

namespace App\Modules\Core\Patients\Data\Repositories;

use App\Modules\Core\Patients\Data\Models\Patient;
use App\Modules\Core\Persons\Data\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PatientRepository {

    public function getAll()
    {
        return Patient::all();
    }

    public function get($id)
    {
        return Patient::find($id);
    }

    public function create($data, Person $person)
    {
        $patient = new Patient;
        $patient->fill($data);

        $patient->person()->associate($person);

        $patient->save();

        return $patient;
    }

    public function update($data, Patient $patient)
    {
        $patient->update($data);

        return $patient;
    }

    public function delete(Patient $patient)
    {
        return $patient->delete();
    }

    public function search($searchParameter)
    {
        /*
        $table ='';
        $chasql = '';
        switch ($searchParameter['search_type']) {
            case 'ARVNO':
                $table = 'patient_identifier';
                $chasql ="SELECT DISTINCT * FROM ".$table." WHERE identifier LIKE '%".$searchParameter['search']."%'";

                break;
            case 'NAME':
                $table = 'person_name';
                $chasql ="SELECT DISTINCT  * FROM ".$table." 
                INNER JOIN person ON(person_name.person_id = person.person_id)
                WHERE given_name LIKE '%". $searchParameter['search'] . "%'
                OR middle_name LIKE '%". $searchParameter['search'] . "%' 
                OR family_name LIKE '%". $searchParameter['search'] . "%' ";
                break;
            
            default:
                $table ='person_name';

                $chasql ="SELECT DISTINCT  * FROM ".$table." 
                INNER JOIN person ON(person_name.person_id = person.person_id)
                WHERE given_name LIKE '%". $searchParameter['search'] . "%'
                OR middle_name LIKE '%". $searchParameter['search'] . "%' 
                OR family_name LIKE '%". $searchParameter['search'] . "%' ";
                break;
        }

        //die($chasql);
        $chadata = DB::select(DB::raw($chasql));
        */
        //die();
        if(is_numeric($searchParameter))
        {
            $chadata =Patient::orderBy('date_created', 'desc')
            ->orWhereHas('patientIdentifiers', function ($query) use ($searchParameter){
                $query->where('identifier', '=', $searchParameter );
            })->distinct()->get();

        }else
        {
            $searchParameterb = explode(' ',$searchParameter);
            if(sizeof($searchParameterb) == 1)
            {
                $chadata =Patient::orderBy('date_created', 'desc')
                ->orWhereHas('person', function ($query) use ($searchParameter){
                    $query->whereHas('names', function ($query) use ($searchParameter){
                        $query->where('given_name', 'like', '%'. $searchParameter . '%');
                    });
                })->distinct()->get();

            }
            else if(sizeof($searchParameterb) == 2)
            {
                $chadata =Patient::orderBy('date_created', 'desc')
                ->orWhereHas('person', function ($query) use ($searchParameterb){
                    $query->whereHas('names', function ($query) use ($searchParameterb){
                        $query->where('given_name', '=', $searchParameterb[0] )
                            ->Where('family_name', '=', $searchParameterb[1] );
                    });
                })->distinct()->get();

            }
            else if(sizeof($searchParameterb) == 3)
            {
                $chadata =Patient::orderBy('date_created', 'desc')
                ->orWhereHas('person', function ($query) use ($searchParameterb){
                    $query->whereHas('names', function ($query) use ($searchParameterb){
                        $query->where('given_name', '=', $searchParameterb[0])
                            ->Where('middle_name', '=', $searchParameterb[1])
                            ->Where('family_name', '=',  $searchParameterb[2]);
                    });
                })->distinct()->get();

            }
            else if(sizeof($searchParameterb) > 3){
                
                $chadata =Patient::orderBy('date_created', 'desc')
                ->orWhereHas('person', function ($query) use ($searchParameterb){
                    $query->whereHas('names', function ($query) use ($searchParameterb){
                        $query->where('given_name', '=', $searchParameterb[0] )
                            ->Where('middle_name', 'like', '%'. $searchParameterb[1] . '%')
                            ->Where('family_name', 'like', '%'. $searchParameterb[2] . '%')
                            ->Where('family_name', 'like', '%'. $searchParameterb[3] . '%');
                    });
                })->distinct()->get();

            
            }
            else
            {
                $chadata =Patient::orderBy('date_created', 'desc')
                ->orWhereHas('person', function ($query) use ($searchParameter){
                    $query->whereHas('names', function ($query) use ($searchParameter){
                        $query->where('given_name','like', '%'. $searchParameter . '%' )
                            ->orWhere('middle_name', 'like', '%'. $searchParameter . '%')
                            ->orWhere('family_name', 'like', '%'. $searchParameter . '%');
                           
                    });
                })->distinct()->get();

            }
            

        }
            

            

        return $chadata;
        
        
    }
}