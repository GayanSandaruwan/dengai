@extends('layouts.admin-lite')
@section('title')
    Prediction Map
@endsection
@section('additional-css')
    <link rel="stylesheet" href="{{asset('/range-slider/css/iThing.css')}}">
@endsection
@section('page-header')
    Comparison Chart
@endsection
@section('optional-header')
@endsection
@section('level')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Comparison</a></li>
        <li class="active">Chart view</li>
    </ol>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12" id="line-chart">
                                <div class="box box-info">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Comparison Chart</h3>

                                        <div class="box-tools pull-right"> Select Moh Area
                                                <select id="moh" name="moh" class="form-control select2"  data-placeholder="Select a Moh Area"
                                                                           style="width: 100%;">
                                                    @foreach($moh_areas as $moh_area)
                                                        <option value="{{$moh_area->moh}}">{{$moh_area->moh}}</option>
                                                    @endforeach
                                                </select>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="chart">
                                            <canvas id="lineChart" style="height:330px"></canvas>
                                        </div>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-1" style="padding-right: 0px; padding-left: 20px">
                                <a href="#" id="previous_year" class="next">&laquo; 2011</a>
                            </div>
                            <div class="col-md-10">
                                <div id="date-range-slider" style="padding: 0px"></div>
                            </div>
                            <div class="col-md-1" style="padding-left: 0px">
                                <a href="#" id="next_year" class="next">2013 &raquo;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="no-data-error-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center">Error getting some data</h4>
                </div>
                <div class="modal-body">
                    <img src="/images/error.png" class="center-block" style="width: 100px">
                    <h4 class="modal-title text-center">OOPS! AI model experienced error getting live stream data for the selected time region. Please check later!</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
@endsection
@section('additional-scripts')

    {{--<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>--}}
    <script src="{{asset('/admin-lte/bower_components/jquery-ui/jquery-ui.js')}}"></script>

    <script src="{{asset('/range-slider/js/jQAllRangeSliders-withRuler-min.js')}}"></script>
    <script src="{{asset('/admin-lte/bower_components/chart.js/Chart.js')}}"></script>

    <script>
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];
        var current_year = 2013;
        var current_month = 0;
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

        var start_year=2013,start_week=1,end_year=2013,end_week=52,moh="Panadura";

        document.addEventListener("DOMContentLoaded", function(event) {
            initPage()
        });

        function initPage(){
            initDateRangeSlider();
            $('.sidebar-toggle').click();
            previewData(start_year,start_week,end_year,end_week,moh);
        }
        $('.select2').select2();

        $("#previous_year").click(function (e) {
            e.preventDefault();
            // current_year--;
            $("#previous_year").text("« "+(current_year-1));
            $("#next_year").text((current_year+1)+" »");
            decreaseBounds();
        });
        $("#next_year").click(function (e) {
            e.preventDefault();
            // current_year++;
            $("#previous_year").text("« "+(current_year-1));
            $("#next_year").text((current_year+1)+" »");
            // initDateRangeSlider();
            increaseBounds();
        });
        // This event will not ne fired
        $("#date-range-slider").bind("userValuesChanged", function(e, data){


            var moh= $('#moh').val();
            var startWeekArray = getWeekNumber(data.values.min);
            start_year = startWeekArray[0];
            start_week = startWeekArray[1];
            var endWeekArray = getWeekNumber(data.values.max);
            end_year=endWeekArray[0];
            end_week =endWeekArray[1];
            previewData(start_year,start_week,end_year,end_week,moh);

        });
        $("#moh").on('change',function (e) {
            var moh = $('#moh').val();
            previewData(start_year,start_week,end_year,end_week,moh);

        });
        function increaseBounds(){
            if(current_month !== 11){
                current_month++;
            }
            else{
                current_year++;
                current_month =0;
            }
            $("#date-range-slider").dateRangeSlider({

                bounds: {min: firstMonday(current_year,current_month), max:  firstMonday(current_year+1,current_month+1)}
            })
        }
        function decreaseBounds(){
            if(current_month !== 0){
                current_month--;
            }
            else{
                current_year--;
                current_month=11;
            }
            $("#date-range-slider").dateRangeSlider({

                bounds: {min: firstMonday(current_year,current_month), max:  firstMonday(current_year+1,current_month+1)}
            })
        }
        function initDateRangeSlider() {
            $("#date-range-slider").dateRangeSlider({
                range:{
                    min: {months: 1},
                    max: {months: 12}
                },
                step:{
                    days: 7
                },
                bounds: {min: new Date(2012, 11, 31), max: firstMonday(current_year+1,1)},
                defaultValues: {min: new Date(2012, 11, 31), max: new Date(2013, 11, 30)},
                scales: [{
                    first: function(value){ return value; },
                    end: function(value) {return value; },
                    next: function(value){
                        var next = new Date(value);
                        return new Date(next.setMonth(value.getMonth() + 1));
                    },
                    label: function(value){
                        return months[value.getMonth()];
                    },
                    format: function(tickContainer, tickStart, tickEnd){
                        tickContainer.addClass("myCustomClass");
                    },
                },
                    {
                        first: function(val){ return val; },
                        next: function(val){
                            var next = new Date(val);
                            return new Date(next.setDate(val.getDate() + 7))
                        },
                        stop: function(val){ return false; },
                        label: function(){ return ""; },
                        // format: function(tickContainer, tickStart, tickEnd){
                        //     tickContainer.addClass("myCustomClass");
                        // },
                    }
                ]
            });
        }


        function getWeekNumber(d) {
            // Copy date so don't modify original
            d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
            // Set to nearest Thursday: current date + 4 - current day number
            // Make Sunday's day number 7
            d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay()||7));
            // Get first day of year
            var yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            // Calculate full weeks to nearest Thursday
            var weekNo = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            // Return array of year and week number
            return [d.getUTCFullYear(), weekNo];
        }
        function firstMonday (year, month){

            var d = new Date(year, month, 1, 0, 0, 0, 0);
            var day = 0;

            // check if first of the month is a Sunday, if so set date to the second
            if (d.getDay() === 0) {

                day = 2;
                d = d.setDate(day);
                d = new Date(d);
            }

            // check if first of the month is a Monday, if so return the date, otherwise get to the Monday following the first of the month
            else if (d.getDay() !== 1) {

                day = 9-(d.getDay());
                d = d.setDate(day);
                d = new Date(d);
            }
            return d

        }

        function previewData(start_year,start_week,end_year,end_week,moh) {

            console.log(start_week,start_year,end_week,end_year,moh);
            $.ajax({
                /* the route pointing to the post function */
                url: '/comparison/chart',
                type: 'POST',
                /* send the csrf-token and the input to the controller ,trip_id:trip_id */
                data: {_token: CSRF_TOKEN,start_year:start_year,start_week:start_week,end_year:end_year,end_week:end_week,moh:moh},
                dataType: 'JSON',
                /* remind that 'data' is the response of the AjaxController */
                success: function (data) {
                    console.log(data);
                    // var heatMapData = {max:12, data: data};
                    // predictionHeatMapLayer.setData(heatMapData);
                    if(data.real_cases.length ==0){
                        noDataError();
                    }
                    var prepareddata = prepareData(data);
                    drawChart(prepareddata[0],prepareddata[1],prepareddata[2]);
                },
                error:function (jqXHR, textStatus, errorThrown) {
                    alert("We got an error processing the request")
                    console.log(errorThrown);
                }
            });

        }
        function prepareData(data) {
            var realCases=[],predictedCases=[],lables_pred=[], lables_real=[];
            console.log(data.real_cases);

            for(var i=0;i<data.real_cases.length;i++){

                var realCase = data.real_cases[i];
                realCases.push(realCase.cases);
                lables_real.push(realCase.year + "-" + realCase.week);
            }
            for(var i=0;i<data.predicted_cases.length;i++){

                var predCase = data.predicted_cases[i];
                predictedCases.push(predCase.cases);
                lables_pred.push(predCase.year + "-" + predCase.week);
            }

            //TODO lables_real should be handled
            return [realCases,predictedCases,lables_real];
        }
        function drawChart(real_cases,predicted_cases,labels) {

            console.log("displaying chart")
            var lineChartData = {
                labels  : labels,
                datasets: [
                    {
                        label               : 'Real Incidents',
                        fillColor           : 'rgba(210, 214, 222, 1)',
                        strokeColor         : 'rgba(210, 214, 222, 1)',
                        pointColor          : 'rgba(210, 214, 222, 1)',
                        pointStrokeColor    : '#c1c7d1',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'rgba(220,220,220,1)',
                        data                : real_cases
                    },
                    {
                        label               : 'Predicted Incidents',
                        fillColor           : 'rgba(60,141,188,0.9)',
                        strokeColor         : 'rgba(60,141,188,0.8)',
                        pointColor          : '#3b8bba',
                        pointStrokeColor    : 'rgba(60,141,188,1)',
                        pointHighlightFill  : '#fff',
                        pointHighlightStroke: 'rgba(60,141,188,1)',
                        data                : predicted_cases
                    }
                ]
            }

            var lineChartOptions = {
                //Boolean - If we should show the scale at all
                showScale               : true,
                //Boolean - Whether grid lines are shown across the chart
                scaleShowGridLines      : true,
                //String - Colour of the grid lines
                scaleGridLineColor      : 'rgba(0,0,0,.05)',
                //Number - Width of the grid lines
                scaleGridLineWidth      : 1,
                //Boolean - Whether to show horizontal lines (except X axis)
                scaleShowHorizontalLines: true,
                //Boolean - Whether to show vertical lines (except Y axis)
                scaleShowVerticalLines  : true,
                //Boolean - Whether the line is curved between points
                bezierCurve             : true,
                //Number - Tension of the bezier curve between points
                bezierCurveTension      : 0.3,
                //Boolean - Whether to show a dot for each point
                pointDot                : true,
                //Number - Radius of each point dot in pixels
                pointDotRadius          : 4,
                //Number - Pixel width of point dot stroke
                pointDotStrokeWidth     : 1,
                //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                pointHitDetectionRadius : 20,
                //Boolean - Whether to show a stroke for datasets
                datasetStroke           : true,
                //Number - Pixel width of dataset stroke
                datasetStrokeWidth      : 2,
                //Boolean - Whether to fill the dataset with a color
                datasetFill             : true,
                //String - A legend template
                legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio     : true,
      //Boolean - whether to make the chart responsive to window resizing
      responsive              : true
    }
        //-------------
        //- LINE CHART -
        //--------------
        var lineChartCanvas          = $('#lineChart').get(0).getContext('2d')
        var lineChart                = new Chart(lineChartCanvas)
        lineChartOptions.datasetFill = false
        lineChart.Line(lineChartData, lineChartOptions)


        }
        function noDataError(){
                    $('#no-data-error-modal').modal('show');
                }
    </script>

@endsection
