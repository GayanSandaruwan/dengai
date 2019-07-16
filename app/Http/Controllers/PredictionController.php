<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PredictionController extends Controller
{
    //

    public function predictionMap(Request $request){

        return view('prediction-map');
    }

    public function getPredictionForWeek(Request $request){
        $year = $request->year;
        $week = $request->week;
        $algorithm = 'random';

        $predictions = DB::table('moh_locations')
            ->join('moh_dengue_predictions','moh_locations.moh',"=",'moh_dengue_predictions.moh')
            ->select('moh_dengue_predictions.cases','moh_locations.lat','moh_locations.lon','moh_locations.moh')
            ->where('moh_dengue_predictions.year',"=",$year)
            ->where('moh_dengue_predictions.week','=',$week)
            ->where('algorithm','=',$algorithm)
            ->get();

        return $predictions;
    }

    public function comparisonMap(Request $request){

        return view('comparison-map');
    }
    public function getRealDataForWeek(Request $request){
        $year = $request->year;
        $week = $request->week;

        $realCases = DB::table('moh_locations')
            ->join('moh_dengue_cases','moh_locations.moh',"=",'moh_dengue_cases.moh')
            ->select('moh_dengue_cases.cases','moh_locations.lat','moh_locations.lon','moh_locations.moh')
            ->where('moh_dengue_cases.year',"=",$year)
            ->where('moh_dengue_cases.week','=',$week)
            ->get();

        return $realCases;
    }

    public function comparisonChart(Request $request){

        $moh_areas = DB::table('moh_locations')
            ->select('moh')->get();

        return view('comparison-chart')->with('moh_areas',$moh_areas);
    }

    public function getComparisonDataforMoh(Request $request){
        $moh = $request->moh;
        $start_week = $request->start_week;
        $start_year = $request->start_year;

        $end_week = $request->end_week;
        $end_year = $request->end_year;
        $max_week_no_of_Year = 52;
        $min_week_no_of_year = 1;

        $algorithm = "random";              //TODO get the algorithm

        if ($start_year == $end_year){
            $realCases = DB::table('moh_dengue_cases')
                ->select('cases','week','year')
                ->where('year','=',$start_year)
                ->where('week','>=',$start_week)
                ->where('week','<=',$end_week)
                ->where('moh','=',$moh)
                ->get();
            $predCases = DB::table('moh_dengue_predictions')
                ->select('cases','week','year')
                ->where('year','=',$start_year)
                ->where('week','>=',$start_week)
                ->where('week','<=',$end_week)
                ->where('moh','=',$moh)
                ->where('algorithm','=',$algorithm)

                ->get();
        }
        else{
            $realCases1 = DB::table('moh_dengue_cases')
                ->select('cases','week','year')
                ->where('year','=',$start_year)
                ->where('week','>=',$start_week)
                ->where('week','<=',$max_week_no_of_Year)
                ->where('moh','=',$moh);


            $realCases2 = DB::table('moh_dengue_cases')
                ->select('cases','week','year')
                ->where('year','=',$end_year)
                ->where('week','>=',$min_week_no_of_year)
                ->where('week','<=',$end_week)
                ->where('moh','=',$moh);


            $realCases = $realCases1->union($realCases2)->get();

            $predCases1 = DB::table('moh_dengue_predictions')
                ->select('cases','week','year')
                ->where('year','=',$start_year)
                ->where('week','>=',$start_week)
                ->where('week','<=',$max_week_no_of_Year)
                ->where('moh','=',$moh)
                ->where('algorithm','=',$algorithm);



            $predCases2 = DB::table('moh_dengue_predictions')
                ->select('cases','week','year')
                ->where('year','=',$end_year)
                ->where('week','>=',$min_week_no_of_year)
                ->where('week','<=',$end_week)
                ->where('moh','=',$moh)
                ->where('algorithm','=',$algorithm);



            $predCases = $predCases1->union($predCases2)->get();
        }

        return ["real_cases"=>$realCases,"predicted_cases"=>$predCases];
    }
}
