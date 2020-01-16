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


        return 0;
    }

    public function filterAge($results,$startAgeDate,$endAgeDate,$gender)
    {   
        $count =0;
        //echo $startAgeDate.' : '.$endAgeDate.' <br>';
        $results2 = array();
        for ($i=0; $i < sizeof($results); $i++) { 
            $chadate = Carbon::parse($results[$i]->birthdate);
            if($startAgeDate !='')
            {
                if($chadate->between($startAgeDate,$endAgeDate) && $results[$i]->gender == $gender)
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
                if($chadate->lt($endAgeDate) && $results[$i]->gender == $gender)
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
                $data = $this->filterAge($results,Carbon::today()->subYears(19),Carbon::today()->subYears(15),$gender);
                break;
            case '20-24':
                $data = $this->filterAge($results,Carbon::today()->subYears(24),Carbon::today()->subYears(20),$gender,);
                break;
            case '25-29':
                $data = $this->filterAge($results,Carbon::today()->subYears(29),Carbon::today()->subYears(25),$gender);
                break;
            case '30-34':
                $data = $this->filterAge($results,Carbon::today()->subYears(34),Carbon::today()->subYears(30),$gender);
                break;
            case '35-39':
                $data = $this->filterAge($results,Carbon::today()->subYears(39),Carbon::today()->subYears(35),$gender);
                break;
            case '40-44':
                $data = $this->filterAge($results,Carbon::today()->subYears(44),Carbon::today()->subYears(40),$gender);
                break;
            case '45-49':
                $data = $this->filterAge($results,Carbon::today()->subYears(49),Carbon::today()->subYears(45),$gender);
                break;
            case '50+':
                $data = $this->filterAge($results,'',Carbon::today()->subYears(50),$gender);
                break;
            case '<1':
                $data = $this->filterAge($results,Carbon::today()->subYears(1),Carbon::today(),$gender);
                break;
            case '1-4':
                $data = $this->filterAge($results,Carbon::today()->subYears(4),Carbon::today()->subYears(1),$gender);
                break;
            case '5-9':
                $data = $this->filterAge($results,Carbon::today()->subYears(9),Carbon::today()->subYears(5),$gender);
                break;
            case '10-14':
                $data = $this->filterAge($results,Carbon::today()->subYears(14),Carbon::today()->subYears(10),$gender);
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
        if(is_null($data['reportStartDate']) || $data['reportStartDate'] == "null"){
            $startDate = Carbon::parse("1900-01-01 00:00:00");
            $endDate = Carbon::parse($data['reportEndDate']." 23:59:59");
        }else{
            $startDate = Carbon::parse($data['reportStartDate']);
            $endDate = Carbon::parse($data['reportEndDate']." 23:59:59");
        }
        switch ($type) {
            case 'txCurrent':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'defaulted1Month':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'defaulted2Months':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'defaulted3MonthsPlus':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'stopped':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'died':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'transferredOut':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            case 'transferredIn':
                $sql = "SELECT distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 
                    from person p 
                    inner join patient_identifier i ON i.patient_id = p.person_id
                    inner join obs on obs.person_id = p.person_id AND concept_id = 56
                    inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
                    where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."'";
                break;
            
            default:
                # code...
                break;
        }

        return $sql;
    }



    public function indicators ($data,$type){

        $results = DB::select(DB::raw($this->getSql($type,$data)));
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
        $data = $this->getGenderDisaggregatedCount($data_f["Data"],"F","10-14");
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
            "males" =>$this->getUnknownCount($results,'M'),
            "females" => $this->getUnknownCount($results,'M')
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
        $pedstotals = $this->calculategrouptotal($txCur['pediatrics']);;
        $txCur['pediatrics']['males']['count'] = $pedstotals['males'];
        $txCur['pediatrics']['females']['count'] = $pedstotals['females'];
        $txCur['pediatrics']['count'] = $txCur['pediatrics']['males']['count'] + $txCur['pediatrics']['females']['count'] ;
        
        //Correcting totals of unknown age
        $unknowagetotal = $txCur['unknownAge']['males'] +  $txCur['unknownAge']['females'];
        
        $txCur['total'] = $txCur['adults']['count'] + $txCur['pediatrics']['count'] + $unknowagetotal;

        return $txCur;

    }

    public function run2($data)
    {
        //$this->txCurrent($data);

        $disaggregatedReportPayload = [];

        //App::make(GetTxCurrentDisAggReportSubAction::class)->run($data['reportEndDate'])
        if ($data['code'] == 1)
        {
            $disaggregatedReportPayload = [
                'txCurrent' => $this->indicators($data,'txCurrent'),
                'defaulted1Month' => $this->indicators($data,'defaulted1Month'),
                'defaulted2Months' => $this->indicators($data,'defaulted2Months'),
                'defaulted3MonthsPlus' => $this->indicators($data,'defaulted3MonthsPlus'),
                'stopped' => $this->indicators($data,'stopped'),
                'died' => $this->indicators($data,'died'),
                'transferredOut' => $this->indicators($data,'transferredOut'), 
                
                
            ];

        }elseif($data['code'] == 2)
        {
            $disaggregatedReportPayload = [
                'txNew' => App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'TXNew'),
                'reInitiated' => App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'reInitiated'),
                'transferredIn' => App::make(GetNewEnrollmentsDisAggReportSubAction::class)->run($data['reportStartDate'], $data['reportEndDate'], 'transferredIn'),
                'defaulted1MonthPlus' => App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'],'defaulted1MonthPlus'),
                'defaulted2MonthsPlus' => App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'],'defaulted2MonthsPlus'),
                'defaulted3MonthsPlus' => App::make(GetDefaultersDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'],'defaulted3MonthsPlus'),
                'stopped' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'stopped'),
                'died' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'died'),
                'transferredOut' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run2($data['reportStartDate'], $data['reportEndDate'], 'transferredOut'),
            ];
        }

        return $disaggregatedReportPayload;
    }
}
