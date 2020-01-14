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

    public function getGenderDisaggregatedCount($results,$gender,$agegroup){
        return 0;
    }
    private function calculategrouptotal($group){
        $malevalues = array_values($group['males']['disaggregatedByAge']) ;
        $maletotal = array_reduce($malevalues,function($a, $b){ return $a + $b;});

        $femalevalues = array_values($group['females']['disaggregatedByAge']) ;
        $femaletotal = array_reduce($femalevalues,function($a, $b){ return $a + $b;});

        return array( 'total' => $maletotal + $femaletotal, 'males' => $maletotal , 'females' => $femaletotal);


    }
    public function txCurrent($data){

        if(is_null($data['reportStartDate']) || $data['reportStartDate'] == "null"){
            $startDate = Carbon::parse("1900-01-01 00:00:00");
            $endDate = Carbon::parse($data['reportEndDate']);
        }else{
            $startDate = Carbon::parse($data['reportStartDate']);
            $endDate = Carbon::parse($data['reportEndDate']." 23:59:59");
        }

       

        $sql = "
            SELECT 
             distinct p.person_id ,TIMESTAMPDIFF(year, p.birthdate, date(obs.value_datetime)) years, i.identifier,t.value_text,  p.* 

            from person p 
            inner join patient_identifier i ON i.patient_id = p.person_id
            inner join obs on obs.person_id = p.person_id AND concept_id = 56
            inner join obs t on t.person_id = p.person_id AND t.concept_id = 55
            where obs.voided = 0 AND i.identifier_type = 4 and obs.value_datetime between '".$startDate."' AND '".$endDate."' 
             ";


        $results = DB::select(DB::raw($sql));
        $adults = array(
            "count"=>null,
            "males" =>array(
                "count" => null,
                "disaggregatedByAge" => array(
                            "15-19" =>  $this->getGenderDisaggregatedCount($results,"M","15-19"),
                            "20-24" =>  $this->getGenderDisaggregatedCount($results,"M","20-24"),
                            "25-29" =>  $this->getGenderDisaggregatedCount($results,"M","25-29"),
                            "30-34" => $this->getGenderDisaggregatedCount($results,"M","30-34"),
                            "35-39" => $this->getGenderDisaggregatedCount($results,"M","35-39"),
                            "40-44" => $this->getGenderDisaggregatedCount($results,"M","40-44"),
                            "45-49" => $this->getGenderDisaggregatedCount($results,"M","45-49"),
                            "50+"=> $this->getGenderDisaggregatedCount($results,"M","50+")
                )
            ),
            "females" => array(
                "count" => null,
                "disaggregatedByAge" => array(
                            "15-19" =>  $this->getGenderDisaggregatedCount($results,"F","15-19"),
                            "20-24" =>  $this->getGenderDisaggregatedCount($results,"F","20-24"),
                            "25-29" =>  $this->getGenderDisaggregatedCount($results,"F","25-29"),
                            "30-34" => $this->getGenderDisaggregatedCount($results,"F","30-34"),
                            "35-39" => $this->getGenderDisaggregatedCount($results,"F","35-39"),
                            "40-44" => $this->getGenderDisaggregatedCount($results,"F","40-44"),
                            "45-49" => $this->getGenderDisaggregatedCount($results,"F","45-49"),
                            "50+"=> $this->getGenderDisaggregatedCount($results,"F","50+")
                )
            )
        );

        $peds = array(
            "count"=> null,
            "males" =>array(
                "count" =>  null,
                "disaggregatedByAge" => array(
                            "<1" =>  0,
                            "1-4" =>  0,
                            "5-9" =>  0,
                            "10-14" => 0
                )
            ),
            "females" => array(
                "count" =>  null,
                "disaggregatedByAge" => array(
                            "<1" =>  0,
                            "1-4" =>  0,
                            "5-9" =>  0,
                            "10-14" => 0
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
        $this->txCurrent($data);

        $disaggregatedReportPayload = [];

        //App::make(GetTxCurrentDisAggReportSubAction::class)->run($data['reportEndDate'])
        if ($data['code'] == 1)
        {
            $disaggregatedReportPayload = [
                'txCurrent' => $this->txCurrent($data) ,
                'defaulted1Month' => App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted1Month'),
                'defaulted2Months' => App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted2Months'),
                'defaulted3MonthsPlus' => App::make(GetDefaultersDisAggReportSubAction::class)->run($data['reportEndDate'],'defaulted3MonthsPlus'),
                'stopped' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'stopped'),
                'died' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'died'),
                'transferredOut' => App::make(GetAdverseOutcomeDisAggReportSubAction::class)->run($data['reportEndDate'], 'transferredOut'),
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
