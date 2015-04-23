/*global window, $, document */
$(document).ready(function() {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_room_type_type_places').TouchSpin({
        min: 1,
        max: 20,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    $('#mbh_bundle_hotelbundle_room_type_type_additionalPlaces, .spinner').TouchSpin({
        min: 0,
        max: 10,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    //pick-a-color
    $("#mbh_bundle_hotelbundle_room_type_type_color").pickAColor();
        var counter = 1;
        //roomType rooms datatables
        $('.rooms-table').each(function() {

            window.addExcelButtons($(this).dataTable({
                "processing": true,
                "serverSide": true,
                "ordering": false,
                "bAutoWidth": false,
                "ajax": Routing.generate('room_type_room_json', {'id': $(this).attr('data-room-type-id')})
            }), counter);
            ++counter
        });

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var counter = 1;
        $('.rooms-table').each(function() {
            $(this).dataTable().fnDestroy();
            window.addExcelButtons($(this).dataTable({
                "processing": true,
                "serverSide": true,
                "ordering": false,
                "bAutoWidth": false,
                "ajax": Routing.generate('room_type_room_json', {'id': $(this).attr('data-room-type-id')})
            }), counter);
            ++counter
        });
    });





});

