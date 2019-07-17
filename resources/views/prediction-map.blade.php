@extends('layouts.admin-lite')
@section('title')
    Prediction Map
    @endsection
@section('additional-css')
    <link rel="stylesheet" href="{{asset('/range-slider/css/iThing.css')}}">
@endsection
@section('page-header')
    Prediction Map
    @endsection
@section('optional-header')
    @endsection
@section('level')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Prediction</a></li>
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
                            <div class="col-md-6" id="heatmap"></div>
                            <div class="col-md-6" id="markermap"></div>
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
    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #heatmap {
            height: 500px;
        }
        #markermap{
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
        var current_year = 2013;
        var heatmap;
        var heatmaplayer;
        var markermap;
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var markers = [];
        var ibArray =[];

        document.addEventListener("DOMContentLoaded", function(event) {
            initMap();
            initDateRangeSlider();
            $('.sidebar-toggle').click()
            previewData(current_year,1)
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
                bounds: {min: firstMonday(current_year,0), max: firstMonday(current_year+1,0)},
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
            heatmap = new google.maps.Map(document.getElementById('heatmap'), {
                center: {lat: 7.4, lng: 80.4},
                zoom: 8
            });

            markermap = new google.maps.Map(document.getElementById('markermap'), {
                center: {lat: 7.4, lng: 80.4},
                zoom: 8
            });
            heatmaplayer = new HeatmapOverlay(heatmap,
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
            var heatmapZoom,heatmapCenter,markermapZoom,markermapCenter;
            heatmap.addListener('mouseover', function() {
                google.maps.event.removeListener(listener2);
                listener1 = google.maps.event.addListener(heatmap, 'bounds_changed', (function() {
                    if(heatmapCenter !== heatmap.getCenter() || heatmapZoom !== heatmap.getZoom()){
                        markermap.setCenter(heatmap.getCenter());
                        markermap.setZoom(heatmap.getZoom());
                        heatmapCenter = heatmap.getCenter();
                        heatmapZoom = heatmap.getZoom();
                        // console.log("heatmap");
                    }

                }));
            });


            markermap.addListener('mouseover', function() {
                google.maps.event.removeListener(listener1);
                listener2 = google.maps.event.addListener(markermap, 'bounds_changed', (function() {
                    if(markermapZoom !== markermap.getZoom() || markermapCenter !== markermap.getCenter()){
                        heatmap.setCenter(markermap.getCenter());
                        heatmap.setZoom(markermap.getZoom());
                        // console.log("markermap");
                        markermapZoom = markermap.getZoom();
                        markermapCenter = markermap.getZoom();
                    }

                }));
            });

            google.maps.event.addListener(markermap, "click", function(event) {
                for (var i = 0; i < ibArray.length; i++ ) {  //I assume you have your infoboxes in some array
                    ibArray[i].close();
                }
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
                    if(data.length ==0){
                        noDataError();
                    }
                    var heatMapData = {max:10, data: data};
                    heatmaplayer.setData(heatMapData);

                    removeOldMarkers();
                    data.forEach(incident => addMarker(incident));
                },
                error:function (jqXHR, textStatus, errorThrown) {
                    alert("We got an error processing the request")
                    console.log(errorThrown);
                }
            });
        }

        function addMarker(loc) {
            var marker = new google.maps.Marker({
                position: {lat: parseFloat(loc["lat"]), lng: parseFloat(loc["lon"])},
                icon:"{{asset('/images/cross-icon.png')}}",
                map: markermap,
                // tittle: loc['moh']
            });

            var cases = loc["cases"];
            console.log(cases);
            var infowindow = new google.maps.InfoWindow({
                content:
                '<p>' +
                    '<strong>Moh Area : </strong>: '+loc["moh"]+'<br/>' +
                    '<strong>Number of Cases : </strong>: '+loc["cases"]+'<br/>' +
                '</p>'
            });
            marker.addListener('click', function() {
                infowindow.open(markermap, marker);
            });
            markers.push(marker);
            ibArray.push(infowindow);
        }
        function removeOldMarkers() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            ibArray = [];
        }
        function noDataError(){
            $('#no-data-error-modal').modal('show');
        }
    </script>

@endsection
