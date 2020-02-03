<?php

namespace App\Modules\Priority\Reports\Processing\Actions;

use App\Modules\Priority\Reports\Processing\SubActions\DisaggregatedReports\GetAdverseOutcomeDisAggReportSubAction;
use App\Modules\Priority\Reports\Processing\SubActions\DisaggregatedReports\GetDefaultersDisAggReportSubAction;
use App\Modules\Priority\Reports\Processing\SubActions\DisaggregatedReports\GetNewEnrollmentsDisAggReportSubAction;
use App\Modules\Priority\Reports\Processing\SubActions\DisaggregatedReports\GetTxCurrentDisAggReportSubAction;
use Illuminate\Support\Facades\App;

use  Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetDisaggregatedReportAction
{
    public function run($data)
    {
        $reportPayload = null;

        if ($data['code'] == 1)
        {
            if ($data['type'] == 'TXCurrent')
                $disaggregatedReportPayload = App::make(GetTxCurrentDisAggReportSubAction::class)->run($data['reportEndDate']);
            elseif ($data['type'] == 'defaulted1Month')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted1Month');
            elseif($data['type'] == 'defaulted2Months')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted2Months');
            elseif($data['type'] == 'defaulted3MonthsPlus')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted3MonthsPlus');
            elseif($data['type'] == 'stopped')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'stopped');
            elseif($data['type'] == 'transferredOut')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'transferredOut');
            elseif($data['type'] == 'totalRegistered')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'totalRegistered');
            else
                $disaggregatedReportPayload = App::make(GetTxCurrentDisAggReportSubAction::class)->run($data['reportEndDate']);

        }else
        {
            if ($data['type'] == 'TXNew')
                $disaggregatedReportPayload = App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'TXNew');
            elseif ($data['type'] == 'reInitiated')
                $disaggregatedReportPayload = App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'reInitiated');
            elseif($data['type'] == 'transferredIn')
                $disaggregatedReportPayload = App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'transferredIn');
            elseif($data['type'] == 'defaulted1MonthPlus')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'defaulted1MonthPlus');
            elseif($data['type'] == 'defaulted2MonthsPlus')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'defaulted2MonthsPlus');
            elseif($data['type'] == 'defaulted3MonthsPlus')
                $disaggregatedReportPayload = App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'defaulted3MonthsPlus');
            elseif($data['type'] == 'stopped')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'stopped');
            elseif($data['type'] == 'died')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'died');
            elseif($data['type'] == 'transferredOut')
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'transferredOut');
            else
                $disaggregatedReportPayload = App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'transferredOut');
        }

        return $disaggregatedReportPayload;
    }


    public function getUnknownCount($results,$gender){
        $count = 0;
        for ($i=0; $i < sizeof($results); $i++) { 

            if($results[$i]->gender == $gender)
            {
                

                $count = $count + 1;
            }

        }


        return $count;
    }

    public function filterAge($results,$startAge,$endAge,$gender)
    {   
        $count =0;
        //echo $startAgeDate.' : '.$endAgeDate.' <br>';
        $results2 = array();
        for ($i=0; $i < sizeof($results); $i++) { 
            
            if($endAge !='')
            {
                if($results[$i]->years >= $startAge && $results[$i]->years <= $endAge && $results[$i]->gender == $gender && $results[$i]->birthdate != '')
                {
                    
                    $count = $count + 1;
                }
                else
                {
                    array_push($results2, $results[$i]);

                }
            }
            else
            {
                //greater than 50 years
                if( $results[$i]->years >= $startAge && $results[$i]->gender == $gender && $results[$i]->birthdate != '')
                {
                    $count = $count + 1;
                }
                else
                {
                    array_push($results2, $results[$i]);

                }
            }
            
        }


        return array("Count"=>$count,"Data"=>$results2);
    }

    public function getGenderDisaggregatedCount($results,$gender,$agegroup){
        switch ($agegroup) {
            case '15-19':
                $data = $this->filterAge($results,15,19,$gender);
                break;
            case '20-24':
                $data = $this->filterAge($results,20,24,$gender);
                break;
            case '25-29':
                $data = $this->filterAge($results,25,29,$gender);
                break;
            case '30-34':
                $data = $this->filterAge($results,30,34,$gender);
                break;
            case '35-39':
                $data = $this->filterAge($results,35,39,$gender);
                break;
            case '40-44':
                $data = $this->filterAge($results,40,44,$gender);
                break;
            case '45-49':
                $data = $this->filterAge($results,45,49,$gender);
                break;
            case '50+':
                $data = $this->filterAge($results,50,'',$gender);
                break;
            case '<1':
                $data = $this->filterAge($results,0,1,$gender);
                break;
            case '1-4':
                $data = $this->filterAge($results,1,4,$gender);
                break;
            case '5-9':
                $data = $this->filterAge($results,5,9,$gender);
                break;
            case '10-14':
                $data = $this->filterAge($results,10,14,$gender);
                break;


            
            default:
                # code...
                break;
        }
        return $data;
    }
    private function calculategrouptotal($group){

        $malevalues = array_values($group['males']['disaggregatedByAge']);
        $maletotal = array_reduce($malevalues,function($a, $b){ return $a + $b;});

        $femalevalues = array_values($group['females']['disaggregatedByAge']) ;
        $femaletotal = array_reduce($femalevalues,function($a, $b){ return $a + $b;});

        return array( 'total' => $maletotal + $femaletotal, 'males' => $maletotal , 'females' => $femaletotal);


    }

    private function getSql($type,$data)
    {
        if($data['reportStartDate'] == "null"){
            $startDate = Carbon::parse("1900-01-01 00:00:00");
            $endDate = Carbon::parse($data['reportEndDate']." 23:59:59");
        }else{
            $startDate = Carbon::parse($data['reportStartDate']);
            $endDate = Carbon::parse($data['reportEndDate']." 23:59:59");
        }
        switch ($type) {
            case 'txCurrent':
                $sql = "SELECT p.person_id, i.identifier arv_number, o.obs_datetime, 
                  o.value_text, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47) app,
                  TIMESTAMPDIFF(month, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47), date('".$endDate."')) diff,
                  TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, p.gender, p.birthdate
                FROM person p
                LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                LEFT join obs o ON o.person_id = p.person_id
                LEFT JOIN obs r ON r.person_id = p.person_id AND r.concept_id = 56 
                WHERE o.concept_id=48 and o.obs_datetime <= '".$endDate."'
                AND o.value_text IS NULL
                and r.value_datetime between '".$startDate."' AND '".$endDate."'
                GROUP BY p.person_id HAVING (diff <= 1) 
                ORDER BY o.obs_datetime DESC";
                break;
            case 'reInitiated':
                $sql = "SELECT 
                      p.person_id , i.identifier arv_number, TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, 
                      i.identifier,t.value_text,  p.gender, p.birthdate
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    inner join encounter e ON e.patient_id = p.person_id
                    AND obs.encounter_id = e.encounter_id
                    AND t.encounter_id = e.encounter_id
                    AND e.encounter_type = 1 AND e.voided = 0
                    where obs.voided = 0 AND i.identifier_type = 4 AND t.value_text = 'Reinitiation'
                    and obs.value_datetime between '".$startDate."' AND '".$endDate."'
                    GROUP BY p.person_id";
                break;
            case 'txNew':
                $sql = "SELECT p.person_id , i.identifier arv_number, p.birthdate, TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, 
                    i.identifier, r.value_datetime, rt.value_text r_type, p.gender, p.birthdate
                  FROM person p 
                  inner join patient_identifier i ON i.patient_id = p.person_id
                  LEFT join obs r on r.person_id = p.person_id AND r.concept_id = 56
                  LEFT join obs rt on rt.person_id = p.person_id AND rt.concept_id = 55
                  inner join encounter e ON e.patient_id = p.person_id
                  AND r.encounter_id = e.encounter_id
                  AND rt.encounter_id = e.encounter_id
                  AND e.encounter_type = 1 AND e.voided = 0
                  WHERE i.identifier_type = 4
                  and r.value_datetime BETWEEN '".$startDate."' AND '".$endDate."'
                  GROUP BY p.person_id HAVING r_type = 'First Time Initiation'";
                break;
            case 'defaulted1Month':
                $sql = "SELECT p.person_id, i.identifier arv_number, o.obs_datetime, 
                  o.value_text, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47) app,
                  TIMESTAMPDIFF(month, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47), date('".$endDate."')) diff,
                  TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, p.gender, p.birthdate
                FROM person p
                LEFT join obs o ON o.person_id = p.person_id
                LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                INNER JOIN encounter e ON e.patient_id = p.person_id
                AND e.patient_id = o.person_id 
                AND e.patient_id = i.patient_id AND e.voided = 0
                WHERE o.concept_id=48 and o.obs_datetime <= '".$endDate."'
                AND o.value_text IS NULL
                GROUP BY p.person_id HAVING (diff > 1 AND diff <= 2) 
                ORDER BY o.obs_datetime DESC";
                break;
            case 'defaulted2Months':
                $sql = "SELECT p.person_id, i.identifier arv_number, 
                  o.obs_datetime, o.value_text, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47) app,
                  TIMESTAMPDIFF(month, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47), date('".$endDate."')) diff,
                  TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, p.gender, p.birthdate
                FROM person p
                LEFT join obs o ON o.person_id = p.person_id
                LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                INNER JOIN encounter e ON e.patient_id = p.person_id
                AND e.patient_id = o.person_id 
                AND e.patient_id = i.patient_id AND e.voided = 0
                WHERE o.concept_id=48 and o.obs_datetime <= '".$endDate."'
                AND o.value_text IS NULL
                GROUP BY p.person_id HAVING (diff > 2 AND diff <= 3) 
                ORDER BY o.obs_datetime DESC";
                break;
            case 'defaulted3MonthsPlus':
                $sql = "SELECT p.person_id, i.identifier arv_number, 
                  o.obs_datetime, o.value_text, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47) app,
                  TIMESTAMPDIFF(month, (select max(value_datetime) from obs where person_id=p.person_id and concept_id=47), date('".$endDate."')) diff,
                  TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, p.gender, p.birthdate
                FROM person p
                LEFT join obs o ON o.person_id = p.person_id
                LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                INNER JOIN encounter e ON e.patient_id = p.person_id
                AND e.patient_id = o.person_id 
                AND e.patient_id = i.patient_id AND e.voided = 0
                WHERE o.concept_id=48 and o.obs_datetime <= '".$endDate."'
                AND o.value_text not in('TO','D','Stop')
                GROUP BY p.person_id HAVING diff > 3 OR value_text = 'Def'
                ORDER BY o.obs_datetime DESC";
                break;
            case 'stopped':
                $sql = "SELECT 
                      p.person_id , i.identifier arv_number,
                      TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, t.value_text,  p.gender, p.birthdate 
                    FROM person p 
                    inner join obs t on t.person_id = p.person_id 
                    AND t.concept_id = (SELECT concept_id FROM concept_name WHERE name = 'Adverse Outcome' LIMIT 1)
                    LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                    INNER JOIN encounter e ON e.patient_id = p.person_id
                    AND e.patient_id = t.person_id 
                    AND e.patient_id = i.patient_id AND e.voided = 0
                    WHERE t.voided = 0 AND t.value_text = 'Stop'
                    AND t.obs_datetime <= '".$endDate."'
                    GROUP BY p.person_id ORDER BY t.obs_datetime DESC";
                break;
            case 'died':
                $sql = "SELECT 
                      p.person_id , i.identifier arv_number,
                      TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, t.value_text,  p.gender, p.birthdate
                    FROM person p 
                    inner join obs t on t.person_id = p.person_id 
                    AND t.concept_id = (SELECT concept_id FROM concept_name WHERE name = 'Adverse Outcome' LIMIT 1)
                    LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                    INNER JOIN encounter e ON e.patient_id = p.person_id
                    AND e.patient_id = t.person_id 
                    AND e.patient_id = i.patient_id AND e.voided = 0
                    WHERE t.voided = 0 AND t.value_text = 'D'
                    AND t.obs_datetime <= '".$endDate."'
                    GROUP BY p.person_id ORDER BY t.obs_datetime DESC";
                break;
            case 'transferredOut':
                $sql = "SELECT 
                      p.person_id , i.identifier arv_number,
                      TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, t.value_text,  p.gender, p.birthdate
                    FROM person p 
                    inner join obs t on t.person_id = p.person_id 
                    AND t.concept_id = (SELECT concept_id FROM concept_name WHERE name = 'Adverse Outcome' LIMIT 1)
                    LEFT JOIN patient_identifier i ON i.patient_id = p.person_id AND i.identifier_type = 4
                    INNER JOIN encounter e ON e.patient_id = p.person_id
                    AND e.patient_id = t.person_id 
                    AND e.patient_id = i.patient_id AND e.voided = 0
                    WHERE t.voided = 0 AND t.value_text = 'TO'
                    AND t.obs_datetime <= '".$endDate."'
                    GROUP BY p.person_id ORDER BY t.obs_datetime DESC";
                break;
            case 'transferredIn':
                $sql = "SELECT 
                      p.person_id , i.identifier arv_number,
                      TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, 
                      i.identifier,t.value_text,  p.gender, p.birthdate
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    inner join encounter e ON e.patient_id = p.person_id
                    AND obs.encounter_id = e.encounter_id
                    AND t.encounter_id = e.encounter_id
                    AND e.encounter_type = 1 AND e.voided = 0
                    where obs.voided = 0 AND i.identifier_type = 4 AND t.value_text = 'Transfer In' 
                    and obs.value_datetime between '".$startDate."' AND '".$endDate."'
                    GROUP BY p.person_id";
                break;
            case  'everRegistared':
                $sql = "SELECT i.identifier arv_number, p.gender, p.birthdate, 
                TIMESTAMPDIFF(year, p.birthdate, date('".$endDate."')) years, 
                LEFT(r.value_datetime,10) reg_date, o.value_text outcome, 
                LEFT(o.obs_datetime,10) outcome_datetime, p.person_id patient_id
              FROM person p 
              INNER JOIN (
                SELECT * FROM patient_identifier 
                  WHERE identifier_type = 4 AND identifier IS NOT NULL GROUP BY patient_id
              ) AS i ON i.patient_id = p.person_id
              LEFT JOIN obs r ON r.person_id = p.person_id AND r.concept_id = 56
              LEFT JOIN obs o ON o.person_id = p.person_id AND o.concept_id = 48
              INNER join encounter e ON e.patient_id = p.person_id
              AND r.encounter_id = e.encounter_id
              AND o.encounter_id = e.encounter_id
              AND e.encounter_type = 1 AND e.voided = 0
              AND o.obs_datetime = (SELECT MAX(obs_datetime) FROM obs WHERE person_id = p.person_id AND concept_id = 48 AND obs_datetime <= '".$endDate."')
              WHERE ((r.value_datetime BETWEEN '".$startDate."' AND '".$endDate."') OR r.value_datetime IS NULL)
              GROUP BY i.patient_id ORDER BY i.identifier";
              break;            
            default:
                # code...
                break;
        }

        return $sql;
    }



    public function indicators ($data,$type){

        $results = DB::select($this->getSql($type,$data));
        //$results = DB::select(\DB::raw($this->getSql($type, $data)))->groupBy(\DB::raw('p.person_id'))->get();
        // adding mutually exclusive trick which doubles the processing speed
        $data = $this->getGenderDisaggregatedCount($results,"M","15-19");
        $fiften_ninteen = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","20-24");
        $twenty_twentyfour = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","25-29");
        $twentyfive_twentynine = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","30-34");
        $thirty_thirtyfour = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","35-39");
        $thirtyfive_thirtynine = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","40-44");
        $forty_fortyfour = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","45-49");
        $fortyfive_fortynine = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","50+");
        $fifty_above = $data["Count"];


        $data_f = $this->getGenderDisaggregatedCount($data["Data"],"F","15-19");
        $fiften_ninteen_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","20-24");
        $twenty_twentyfour_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","25-29");
        $twentyfive_twentynine_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","30-34");
        $thirty_thirtyfour_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","35-39");
        $thirtyfive_thirtynine_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","40-44");
        $forty_fortyfour_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","45-49");
        $fortyfive_fortynine_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","50+");
        $fifty_above_f = $data_f["Count"];




        //peads

        $data = $this->getGenderDisaggregatedCount($data_f["Data"],"M","<1");
        $less_one = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","1-4");
        $one_four = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","5-9");
        $five_nine = $data["Count"];
        $data = $this->getGenderDisaggregatedCount($data["Data"],"M","10-14");
        $ten_fourteen = $data["Count"];


        $data_f = $this->getGenderDisaggregatedCount($data["Data"],"F","<1");
        $less_one_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","1-4");
        $one_four_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","5-9");
        $five_nine_f = $data_f["Count"];
        $data_f = $this->getGenderDisaggregatedCount($data_f["Data"],"F","10-14");
        $ten_fourteen_f = $data_f["Count"];





        $adults = array(
            "count"=>null,
            "males" =>array(
                "count" => null,
                "disaggregatedByAge" => array(
                            "15-19" => $fiften_ninteen ,
                            "20-24" => $twenty_twentyfour ,
                            "25-29" => $twentyfive_twentynine ,
                            "30-34" => $thirty_thirtyfour,
                            "35-39" =>  $thirtyfive_thirtynine,
                            "40-44" => $forty_fortyfour,
                            "45-49" =>  $fortyfive_fortynine,
                            "50+"=> $fifty_above
                )
            ),
            "females" => array(
                "count" => null,
                "disaggregatedByAge" => array(
                            "15-19" => $fiften_ninteen_f ,
                            "20-24" => $twenty_twentyfour_f ,
                            "25-29" => $twentyfive_twentynine_f ,
                            "30-34" => $thirty_thirtyfour_f,
                            "35-39" =>  $thirtyfive_thirtynine_f,
                            "40-44" => $forty_fortyfour_f,
                            "45-49" =>  $fortyfive_fortynine_f,
                            "50+"=> $fifty_above_f
                )
            )
        );

        $peds = array(
            "count"=> null,
            "males" =>array(
                "count" =>  null,
                "disaggregatedByAge" => array(
                            "<1" =>  $less_one,
                            "1-4" =>  $one_four,
                            "5-9" =>  $five_nine,
                            "10-14" => $ten_fourteen
                )
            ),
            "females" => array(
                "count" =>  null,
                "disaggregatedByAge" => array(
                            "<1" =>  $less_one_f,
                            "1-4" =>  $one_four_f,
                            "5-9" =>  $five_nine_f,
                            "10-14" => $ten_fourteen_f
                )
            )
        );
        $unknownAge = array(
            "count"=>null,
            "males" =>$this->getUnknownCount($data_f["Data"],'M'),
            "females" => $this->getUnknownCount($data_f["Data"],'F')
        );
        $txCur = array(
            "total"=>"",
            "adults" => $adults,
            "pediatrics"  => $peds,
            "unknownAge" => $unknownAge
        );
        //Correcting totals of adults
        $adulttotals = $this->calculategrouptotal($txCur['adults']);
        $txCur['adults']['males']['count'] = $adulttotals['males'];
        $txCur['adults']['females']['count'] = $adulttotals['females'];
        $txCur['adults']['count'] = $txCur['adults']['males']['count'] + $txCur['adults']['females']['count'] ;

        //Correcting totals of peds      
        $pedstotals = $this->calculategrouptotal($txCur['pediatrics']);
        $txCur['pediatrics']['males']['count'] = $pedstotals['males'];
        $txCur['pediatrics']['females']['count'] = $pedstotals['females'];
        $txCur['pediatrics']['count'] = $txCur['pediatrics']['males']['count'] + $txCur['pediatrics']['females']['count'] ;
        
        //Correcting totals of unknown age
        $unknowagetotal = $txCur['unknownAge']['males'] +  $txCur['unknownAge']['females'];
        
        $txCur['total'] = $txCur['adults']['count'] + $txCur['pediatrics']['count'] + $unknowagetotal;
        $txCur['unknownAge']['count'] = $unknowagetotal;

        return array("patientList"=>$results,"disagg"=>$txCur);

    }

    public function run2($data)
    {
        //$this->txCurrent($data);

        $disaggregatedReportPayload = [];

        //App::make(GetTxCurrentDisAggReportSubAction::class)->run($data['reportEndDate'])
        if ($data['code'] == 1)
        {
            $disaggregatedReportPayload = [
                'txCurrent' => $this->indicators($data,'txCurrent')["disagg"],
                'defaulted1Month' => $this->indicators($data,'defaulted1Month')["disagg"],
                'defaulted2Months' => $this->indicators($data,'defaulted2Months')["disagg"],
                'defaulted3MonthsPlus' => $this->indicators($data,'defaulted3MonthsPlus')["disagg"],
                'stopped' => $this->indicators($data,'stopped')["disagg"],
                'died' => $this->indicators($data,'died')["disagg"],
                'transferredOut' => $this->indicators($data,'transferredOut')["disagg"], 
                'everRegistared' => $this->indicators($data,'everRegistared')["disagg"]
            ];

        }elseif($data['code'] == 2)
        {
            $disaggregatedReportPayload = [
                'txNew' => $this->indicators($data,'txNew')["disagg"],
                'reInitiated' => $this->indicators($data,'reInitiated')["disagg"],
                'transferredIn' => $this->indicators($data,'transferredIn')["disagg"],
                'defaulted1MonthPlus' => $this->indicators($data,'defaulted1Month')["disagg"],
                'defaulted2MonthsPlus' => $this->indicators($data,'defaulted2Months')["disagg"],
                'defaulted3MonthsPlus' => $this->indicators($data,'defaulted3MonthsPlus')["disagg"],
                'stopped' => $this->indicators($data,'stopped')["disagg"],
                'died' => $this->indicators($data,'died')["disagg"],
                'transferredOut' => $this->indicators($data,'transferredOut')["disagg"],
                'everRegistared' => $this->indicators($data,'everRegistared')["disagg"]
            ];
        }

        return $disaggregatedReportPayload;
    }


    public function patientList($data)
    {
        
        $disaggregatedReportPayload = [
            $data['type'] => $this->indicators($data,$data['type'])["patientList"]
          
        ];

       

        return $disaggregatedReportPayload;
    }
}
