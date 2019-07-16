@extends('layouts.admin-lite')
@section('title')
    Prediction Map
@endsection
@section('additional-css')
    <link rel="stylesheet" href="{{asset('/range-slider/css/iThing.css')}}">
@endsection
@section('page-header')
    Comparison Map
@endsection
@section('optional-header')
@endsection
@section('level')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Comparison</a></li>
        <li class="active">Map view</li>
    </ol>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6" id="predictionMap"></div>
                            <div class="col-md-6" id="realMap"></div>
                            <div class="predmap">Prediction Map</div>
                            <div class="realmap">Real Map</div>

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
                        <div class='my-legend'>
                            <div class='legend-title'>Dengue Incident level</div>
                            <div class='legend-scale'>
                                <ul class='legend-labels'>
                                    <li><span style='background:#d30e3d;'></span>> Ten</li>
                                    <li><span style='background:#ff8b1e;'></span>< Ten</li>
                                    <li><span style='background: yellow;'></span>< Eight</li>
                                    <li><span style='background:#14fb28;'></span>< Five</li>
                                    <li><span style='background:#07c6d3;'></span>< Two</li>
                                </ul>
                            </div>
                            {{--<div class='legend-source'>Source: <a href="#link to source">Name of source</a></div>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #predictionMap {
            height: 500px;
        }
        #realMap{
            height: 500px;
        }
        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .no-bottom-margin{
            margin-bottom: 0px;
        }
        .content{
            padding-top: 0px;
        }
        .no-pad{
            padding: 0;
        }
        .predmap{
            position: fixed;
            left: 10%;
            top: 50%;
            font-weight: bold;
            font-style: italic;
        }
        .realmap{
            position: fixed;
            left: 60%;
            top: 50%;
            font-weight: bold;
            font-style: italic;

        }
    </style>
@endsection
@section('additional-scripts')

    {{--<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>--}}
    <script src="{{asset('/admin-lte/bower_components/jquery-ui/jquery-ui.js')}}"></script>

    <script src="{{asset('/range-slider/js/jQAllRangeSliders-withRuler-min.js')}}"></script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{env('MAP_API_KEY')}}&libraries=drawing"></script>

    <script src="{{asset('/js/heatmap.js')}}"></script>
    <script src="{{asset('/js/gmaps-heatmap.js')}}"></script>
    <script>
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"];
        var current_year = 2012;
        var predictionMap;
        var predictionHeatMapLayer, realHeatMapLayer;
        var realMap;
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var markers = [];

        document.addEventListener("DOMContentLoaded", function(event) {
            initMap();
            initDateRangeSlider();
            $('.sidebar-toggle').click()
        });

        $("#previous_year").click(function (e) {
            e.preventDefault();
            current_year--;
            $("#previous_year").text("« "+(current_year-1));
            $("#next_year").text((current_year+1)+" »");
            initDateRangeSlider();
        });
        $("#next_year").click(function (e) {
            e.preventDefault();
            current_year++;
            $("#previous_year").text("« "+(current_year-1));
            $("#next_year").text((current_year+1)+" »");
            initDateRangeSlider();
        });
        // This event will not ne fired
        $("#date-range-slider").bind("userValuesChanged", function(e, data){
            console.log(data.values.max);
            var yearWeek = getWeekNumber(data.values.max);
            console.log(yearWeek);
            previewData(yearWeek[0],yearWeek[1]);

        });
        function initDateRangeSlider() {
            $("#date-range-slider").dateRangeSlider({
                range:{
                    min: {days: 7},
                    max: {days: 7}
                },
                step:{
                    days: 7
                },
                bounds: {min: new Date(current_year, 0, 1), max: new Date(current_year+1,0,1)},
                // defaultValues: {min: new Date(current_year, 0, 1), max: new Date(current_year, 0, 8)},
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
        function initMap() {
            predictionMap = new google.maps.Map(document.getElementById('predictionMap'), {
                center: {lat: 7.4, lng: 80.4},
                zoom: 8
            });

            realMap = new google.maps.Map(document.getElementById('realMap'), {
                center: {lat: 7.4, lng: 80.4},
                zoom: 8
            });
            predictionHeatMapLayer = new HeatmapOverlay(predictionMap,
                {
                    // radius should be small ONLY if scaleRadius is true (or small radius is intended)
                    "radius": 0.1,
                    "maxOpacity": 0,
                    // scales the radius based on map zoom
                    "scaleRadius": true,
                    // if set to false the heatmap uses the global maximum for colorization
                    // if activated: uses the data maximum within the current map boundaries
                    //   (there will always be a red spot with useLocalExtremas true)
                    "useLocalExtrema": false,
                    // which field name in your data represents the latitude - default "lat"
                    latField: 'lat',
                    // which field name in your data represents the longitude - default "lng"
                    lngField: 'lon',
                    // which field name in your data represents the data value - default "value"
                    valueField: 'cases'
                }
            );
            realHeatMapLayer = new HeatmapOverlay(realMap,
                {
                    // radius should be small ONLY if scaleRadius is true (or small radius is intended)
                    "radius": 0.1,
                    "maxOpacity": 0,
                    // scales the radius based on map zoom
                    "scaleRadius": true,
                    // if set to false the heatmap uses the global maximum for colorization
                    // if activated: uses the data maximum within the current map boundaries
                    //   (there will always be a red spot with useLocalExtremas true)
                    "useLocalExtrema": false,
                    // which field name in your data represents the latitude - default "lat"
                    latField: 'lat',
                    // which field name in your data represents the longitude - default "lng"
                    lngField: 'lon',
                    // which field name in your data represents the data value - default "value"
                    valueField: 'cases'
                }
            );
            var listener1, listener2;
            var predicionmapZoom,predictionmapCenter,realmapZoom,realmapCenter;
            predictionMap.addListener('mouseover', function() {
                google.maps.event.removeListener(listener2);
                listener1 = google.maps.event.addListener(predictionMap, 'bounds_changed', (function() {
                    if(predictionmapCenter !== predictionMap.getCenter() || predicionmapZoom !== predictionMap.getZoom()){
                        realMap.setCenter(predictionMap.getCenter());
                        realMap.setZoom(predictionMap.getZoom());
                        predicionmapZoom = predictionMap.getZoom();
                        predictionmapCenter = predictionMap.getCenter();
                    }

                }));
            });


            realMap.addListener('mouseover', function() {
                google.maps.event.removeListener(listener1);
                listener2 = google.maps.event.addListener(realMap, 'bounds_changed', (function() {
                    if(realmapCenter !== realMap.getCenter() || realmapZoom !== realMap.getZoom()){
                        predictionMap.setCenter(realMap.getCenter());
                        predictionMap.setZoom(realMap.getZoom());
                        realmapZoom = realMap.getZoom();
                        realmapCenter = realMap.getCenter();
                    }

                }));
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

        function previewData(year,week) {
            $.ajax({
                /* the route pointing to the post function */
                url: '/prediction/map',
                type: 'POST',
                /* send the csrf-token and the input to the controller ,trip_id:trip_id */
                data: {_token: CSRF_TOKEN,year:year,week:week},
                dataType: 'JSON',
                /* remind that 'data' is the response of the AjaxController */
                success: function (data) {
                    console.log(data);

                    var heatMapData = {max:12, data: data};
                    predictionHeatMapLayer.setData(heatMapData);
                },
                error:function (jqXHR, textStatus, errorThrown) {
                    alert("We got an error processing the request")
                    console.log(errorThrown);
                }
            });
            $.ajax({
                /* the route pointing to the post function */
                url: '/realdata/map',
                type: 'POST',
                /* send the csrf-token and the input to the controller ,trip_id:trip_id */
                data: {_token: CSRF_TOKEN,year:year,week:week},
                dataType: 'JSON',
                /* remind that 'data' is the response of the AjaxController */
                success: function (data) {
                    console.log(data);

                    var heatMapData = {max:12, data: data};
                    realHeatMapLayer.setData(heatMapData);
                },
                error:function (jqXHR, textStatus, errorThrown) {
                    alert("We got an error processing the request")
                    console.log(errorThrown);
                }
            });

        }


    </script>

@endsection
