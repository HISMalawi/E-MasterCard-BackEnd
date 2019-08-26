<?php

namespace App\Modules\Priority\Reports\Processing\SubActions\DisaggregatedReports;

use App\Modules\Priority\Reports\Processing\Tasks\GetDisaggregatesTask;
use App\Modules\Priority\Reports\Processing\Tasks\GetLastRegistrationTask;
use App\Modules\Priority\Reports\Processing\Tasks\GetLastVisitEncounterTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class GetDefaultersDisAggReportSubAction
{
    public function run($reportDate, $type)
    {
        $parsedReportDate = Carbon::parse($reportDate);
        ### STILL UNDER WORKS TO SORT BY VISIT DATE ######
        $lastVisitEncounterIDs = App::make(GetLastVisitEncounterTask::class)->run3();

        $eventsQuery = DB::table('visit_outcome_event')
            ->whereIn('encounter_id', $lastVisitEncounterIDs)
            ->whereNull('adverse_outcome')
            ->whereNotNull('next_appointment_date');

        if ($type == 'defaulted1Month')
            $eventsQuery->whereBetween('next_appointment_date', [with(clone $parsedReportDate)->subDays(60),with(clone $parsedReportDate)->subDays(30)]);
        elseif ($type == 'defaulted2Months')
            $eventsQuery->whereBetween('next_appointment_date', [with(clone $parsedReportDate)->subDays(90),with(clone $parsedReportDate)->subDays(60)]);
        else
            $eventsQuery->whereDate('next_appointment_date','<', with(clone $parsedReportDate)->subDays(90));

        return App::make(GetDisaggregatesTask::class)->run($eventsQuery, $parsedReportDate);
    }

    public function run2($reportStartDate, $reportEndDate, $type)
    {
        $parsedReportStartDate = Carbon::parse($reportStartDate);
        $parsedReportEndDate = Carbon::parse($reportEndDate);

        ### STILL UNDER WORKS TO SORT BY VISIT DATE ######
        $lastVisitEncounterIDs = App::make(GetLastVisitEncounterTask::class)->run3();

        $eventsQuery = DB::table('visit_outcome_event')
            ->whereIn('encounter_id', $lastVisitEncounterIDs)
            ->whereNull('adverse_outcome')
            ->whereNotNull('next_appointment_date');

        if($type == 'defaulted1MonthPlus')
            $eventsQuery->whereDate('next_appointment_date','<', with(clone $parsedReportEndDate)->subDays(30));
        elseif ($type == 'defaulted2MonthsPlus')
            $eventsQuery->whereDate('next_appointment_date','<', with(clone $parsedReportEndDate)->subDays(60));
        else
            $eventsQuery->whereDate('next_appointment_date','<', with(clone $parsedReportEndDate)->subDays(90));

        $eventsQuery->whereBetween('next_appointment_date', [$parsedReportStartDate, $parsedReportEndDate]);;

        return App::make(GetDisaggregatesTask::class)->run2($eventsQuery, $parsedReportEndDate, 'AdverseOutcomes ');
    }
}
